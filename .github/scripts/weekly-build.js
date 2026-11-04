/**
 * Weekly build / リリースパッケージの作成ロジック
 *
 * .github/workflows/deploy.yml から actions/github-script 経由で呼び出す。
 * PR タイトル等の外部入力はシェルを経由させず、JS の文字列としてのみ扱う。
 *
 * ローカルテスト: node --test .github/scripts/weekly-build.test.js
 */
'use strict';

const fs = require('node:fs');
const path = require('node:path');

const WEEKLY_PREFIX = 'eccube2-weekly-';
const BC_BREAK_LABEL = 'BC Break';
const ARCHIVE_EXTS = ['tar.gz', 'zip'];
// md5 / sha1 は歴史的経緯 (公式サイトでの MD5 公開) により残している
const CHECKSUM_ALGOS = ['md5', 'sha1', 'sha256'];
// GitHub のリリース本文の上限は 125,000 文字。余裕を持たせる
const MAX_BODY_LENGTH = 120000;

const BODY_HEADER = [
  'EC-CUBE 2.25系の Weekly build🚀 です。毎週の改善内容を反映しております。',
  '常に安定して動作するよう努めていますが、思わぬ不具合を取り込んでしまっている場合もあります。十分に検証の上ご利用ください。',
].join('\n');

/**
 * JST の日付を YYYYMMDD で返す (JST は夏時間が無いので固定オフセットで計算する)
 * @param {Date} date
 */
function jstDateString(date = new Date()) {
  return new Date(date.getTime() + 9 * 60 * 60 * 1000).toISOString().slice(0, 10).replace(/-/g, '');
}

/** @param {string} tagName */
function assetNames(tagName) {
  const names = [];
  for (const ext of ARCHIVE_EXTS) {
    names.push(`${tagName}.${ext}`);
    for (const algo of CHECKSUM_ALGOS) {
      names.push(`${tagName}.${ext}.checksum.${algo}`);
    }
  }
  return names;
}

/**
 * @param {{number: number, title: string, html_url: string}} pr
 */
function formatPr(pr) {
  // 改行が混入しても Markdown の構造 (引用ブロック) を壊さないようにする
  const title = pr.title.replace(/\s+/g, ' ').trim();
  return `${title} #${pr.number} (${pr.html_url})`;
}

/**
 * リリース本文を組み立てる
 * @param {object} p
 * @param {string} p.serverUrl
 * @param {string} p.repository owner/repo
 * @param {string} p.tagName
 * @param {string|null} p.previousWeeklyTag
 * @param {string|null} p.latestReleaseTag
 * @param {Array<{number: number, title: string, html_url: string}>} p.newBcBreaks 前回 weekly 以降の BC Break
 * @param {Array<{number: number, title: string, html_url: string}>} p.allBcBreaks latest リリース以降の BC Break
 * @param {string} p.notes generate-notes の本文
 */
function renderBody(p) {
  const base = `${p.serverUrl}/${p.repository}/releases/download/${p.tagName}`;
  const sections = [BODY_HEADER];

  if (p.newBcBreaks.length > 0) {
    const since = p.previousWeeklyTag ? `前回の Weekly build (${p.previousWeeklyTag}) 以降に、` : '';
    sections.push([
      '> [!WARNING]',
      `> **${since}後方互換性のない変更 (BC Break) が含まれています。**`,
      '> カスタマイズやプラグインへの影響を確認の上、ご利用ください。',
      '>',
      ...p.newBcBreaks.map((pr) => `> - ${formatPr(pr)}`),
    ].join('\n'));
  } else if (p.allBcBreaks.length > 0) {
    const since = p.latestReleaseTag ? `${p.latestReleaseTag} 以降に` : '';
    sections.push([
      '> [!NOTE]',
      `> ${since}後方互換性のない変更 (BC Break) が ${p.allBcBreaks.length} 件含まれています。下記の「BC Break」欄をご確認ください。`,
    ].join('\n'));
  }

  const rows = ARCHIVE_EXTS.map((ext) => {
    const file = `${p.tagName}.${ext}`;
    const sum = `${file}.checksum.sha256`;
    return `<tr><td><a href="${base}/${file}">${file}</a></td><td><a href="${base}/${sum}">${sum}</a></td></tr>`;
  });
  sections.push([
    '<table>',
    '<thead><tr><th>File</th><th>Checksum(sha256)</th></tr></thead>',
    '<tbody>',
    ...rows,
    '</tbody>',
    '</table>',
  ].join('\n'));

  if (p.notes) {
    sections.push(p.notes.trim());
  }
  const body = sections.join('\n\n') + '\n';
  if (body.length <= MAX_BODY_LENGTH) {
    return body;
  }
  // リリース本文の上限 (125,000 文字) を超える場合は Changelog を切り詰めて比較ページへ誘導する
  const compare = p.latestReleaseTag
    ? `${p.serverUrl}/${p.repository}/compare/${p.latestReleaseTag}...${p.tagName}`
    : `${p.serverUrl}/${p.repository}/commits/${p.tagName}`;
  const notice = `\n\n…(Changelog が長すぎるため省略しました。全体は ${compare} を参照してください)\n`;
  return body.slice(0, MAX_BODY_LENGTH - notice.length) + notice;
}

/**
 * ref (タグ名 / SHA) をコミット SHA に解決する
 */
async function resolveSha(github, owner, repo, ref) {
  const { data } = await github.rest.repos.getCommit({ owner, repo, ref, per_page: 1 });
  return data.sha;
}

/**
 * ancestor が head に含まれているか
 */
async function isAncestor(github, owner, repo, ancestor, head) {
  if (ancestor === head) {
    return true;
  }
  try {
    const { data } = await github.rest.repos.compareCommitsWithBasehead({
      owner, repo, basehead: `${ancestor}...${head}`, per_page: 1,
    });
    return data.status === 'ahead' || data.status === 'identical';
  } catch (e) {
    // force-push 等で到達できないコミットは「含まれていない」とみなす
    if (e.status === 404) {
      return false;
    }
    throw e;
  }
}

/**
 * head に取り込まれた BC Break ラベル付き PR のうち、指定 SHA に含まれていないものを返す
 * @param {string[]} excludeShas 各要素ごとに「その SHA に含まれていない PR」を求める
 * @returns {Promise<Array<Array<object>>>} excludeShas と同順の配列
 */
async function findBcBreaks(github, owner, repo, baseBranch, headSha, excludeShas) {
  const items = await github.paginate(github.rest.search.issuesAndPullRequests, {
    q: `repo:${owner}/${repo} is:pr is:merged base:${baseBranch} label:"${BC_BREAK_LABEL}"`,
    per_page: 100,
  });
  const results = excludeShas.map(() => []);
  for (const item of items) {
    const { data: pr } = await github.rest.pulls.get({ owner, repo, pull_number: item.number });
    if (!pr.merge_commit_sha || !(await isAncestor(github, owner, repo, pr.merge_commit_sha, headSha))) {
      continue;
    }
    for (let i = 0; i < excludeShas.length; i++) {
      const sha = excludeShas[i];
      if (!sha || !(await isAncestor(github, owner, repo, pr.merge_commit_sha, sha))) {
        results[i].push({ number: pr.number, title: pr.title, html_url: pr.html_url, merged_at: pr.merged_at });
      }
    }
  }
  for (const list of results) {
    list.sort((a, b) => String(a.merged_at).localeCompare(String(b.merged_at)));
  }
  return results;
}

/**
 * weekly build を作成するかどうかを判定し、リリース名・本文を準備する
 * @returns {Promise<object>} deploy=false の場合は reason に理由を入れる
 */
async function prepareWeekly({ github, context, inputs = {}, now = new Date() }) {
  const { owner, repo } = context.repo;
  const repository = `${owner}/${repo}`;
  const serverUrl = context.serverUrl || 'https://github.com';
  const baseBranch = context.payload.repository?.default_branch || 'master';
  // schedule はデフォルトブランチの HEAD、workflow_dispatch は選択したブランチの HEAD
  const headSha = context.sha;
  const tagName = inputs.tagName || `${WEEKLY_PREFIX}${jstDateString(now)}`;

  let latestRelease = null;
  try {
    ({ data: latestRelease } = await github.rest.repos.getLatestRelease({ owner, repo }));
  } catch (e) {
    if (e.status !== 404) {
      throw e;
    }
  }
  const { data: releases } = await github.rest.repos.listReleases({ owner, repo, per_page: 100 });
  const previousWeekly = releases.find((r) => !r.draft && r.prerelease && r.tag_name.startsWith(WEEKLY_PREFIX)) || null;

  const latestReleaseSha = latestRelease ? await resolveSha(github, owner, repo, latestRelease.tag_name) : null;
  const previousWeeklySha = previousWeekly ? await resolveSha(github, owner, repo, previousWeekly.tag_name) : null;

  const result = {
    deploy: false,
    reason: '',
    tagName,
    headSha,
    latestReleaseTag: latestRelease?.tag_name ?? null,
    previousWeeklyTag: previousWeekly?.tag_name ?? null,
  };

  if (!inputs.force) {
    if (headSha === previousWeeklySha) {
      result.reason = `${baseBranch} (${headSha.slice(0, 7)}) は前回の Weekly build (${previousWeekly.tag_name}) から変更がありません`;
      return result;
    }
    if (headSha === latestReleaseSha) {
      result.reason = `${baseBranch} (${headSha.slice(0, 7)}) は最新リリース (${latestRelease.tag_name}) から変更がありません`;
      return result;
    }
  }

  const published = releases.find((r) => !r.draft && r.tag_name === tagName);
  if (published) {
    throw new Error(`${tagName} は既に公開済みです (${published.html_url})。再作成する場合は手動で削除してから実行してください`);
  }

  const [allBcBreaks, newBcBreaks] = await findBcBreaks(
    github, owner, repo, baseBranch, headSha, [latestReleaseSha, previousWeeklySha],
  );

  const { data: notes } = await github.rest.repos.generateReleaseNotes({
    owner,
    repo,
    tag_name: tagName,
    target_commitish: headSha,
    ...(latestRelease ? { previous_tag_name: latestRelease.tag_name } : {}),
  });

  const body = renderBody({
    serverUrl,
    repository,
    tagName,
    previousWeeklyTag: result.previousWeeklyTag,
    latestReleaseTag: result.latestReleaseTag,
    newBcBreaks,
    allBcBreaks,
    notes: notes.body,
  });

  return {
    ...result,
    deploy: true,
    releaseName: newBcBreaks.length > 0 ? `${tagName} ⚠️ BC Break` : tagName,
    body,
    newBcBreaks,
    allBcBreaks,
  };
}

/**
 * リリースに成果物をアップロードする (同名のアセットは置き換える)
 */
async function uploadAssets({ github, owner, repo, releaseId, distDir, tagName }) {
  const { data: existing } = await github.rest.repos.listReleaseAssets({ owner, repo, release_id: releaseId, per_page: 100 });
  for (const name of assetNames(tagName)) {
    const file = path.join(distDir, name);
    if (!fs.existsSync(file)) {
      throw new Error(`成果物が見つかりません: ${file}`);
    }
    const old = existing.find((a) => a.name === name);
    if (old) {
      await github.rest.repos.deleteReleaseAsset({ owner, repo, asset_id: old.id });
    }
    const data = fs.readFileSync(file);
    await github.rest.repos.uploadReleaseAsset({
      owner,
      repo,
      release_id: releaseId,
      name,
      data,
      headers: {
        'content-type': name.endsWith('.zip') ? 'application/zip'
          : name.endsWith('.tar.gz') ? 'application/gzip' : 'text/plain',
        'content-length': data.length,
      },
    });
  }
}

/**
 * weekly build を公開する
 * 1. 同じタグの draft が残っていれば削除 (前回の失敗の残骸)
 * 2. draft で作成し成果物をアップロード
 * 3. 本文を付けて prerelease として公開 (公開時に target_commitish にタグが作られる)
 * 成果物の無い prerelease が公開されたままになる状態を避けるため、公開は最後に行う
 */
async function publishWeekly({ github, context, prepared, distDir }) {
  const { owner, repo } = context.repo;
  const releases = await github.paginate(github.rest.repos.listReleases, { owner, repo, per_page: 100 });
  for (const r of releases.filter((r) => r.draft && r.tag_name === prepared.tagName)) {
    await github.rest.repos.deleteRelease({ owner, repo, release_id: r.id });
  }

  const { data: draft } = await github.rest.repos.createRelease({
    owner,
    repo,
    tag_name: prepared.tagName,
    target_commitish: prepared.headSha,
    name: prepared.releaseName,
    body: prepared.body,
    draft: true,
    prerelease: true,
  });
  await uploadAssets({ github, owner, repo, releaseId: draft.id, distDir, tagName: prepared.tagName });
  const { data: release } = await github.rest.repos.updateRelease({
    owner, repo, release_id: draft.id, draft: false, prerelease: true,
  });
  return release;
}

/**
 * 判定結果を Job Summary に出力する
 */
async function writeSummary(core, prepared, { dryRun = false, releaseUrl = '' } = {}) {
  const s = core.summary.addHeading(`Weekly build: ${prepared.tagName}`, 2);
  if (!prepared.deploy) {
    s.addRaw(`⏭️ スキップしました: ${prepared.reason}`, true);
  } else {
    s.addList([
      `対象コミット: ${prepared.headSha}`,
      `Changelog の起点: ${prepared.latestReleaseTag ?? '(なし)'}`,
      `前回の Weekly build: ${prepared.previousWeeklyTag ?? '(なし)'}`,
      `BC Break (前回 weekly 以降): ${prepared.newBcBreaks.length} 件`,
      `BC Break (${prepared.latestReleaseTag ?? '全期間'} 以降): ${prepared.allBcBreaks.length} 件`,
      dryRun ? '🧪 dry-run のため公開していません' : `🚀 公開しました: ${releaseUrl}`,
    ]);
    s.addHeading('リリース本文', 3).addCodeBlock(prepared.body, 'markdown');
  }
  await s.write();
}

module.exports = {
  WEEKLY_PREFIX,
  assetNames,
  jstDateString,
  renderBody,
  prepareWeekly,
  publishWeekly,
  uploadAssets,
  writeSummary,
};
