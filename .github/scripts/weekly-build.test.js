'use strict';

// 実行方法: node --test .github/scripts/weekly-build.test.js
const test = require('node:test');
const assert = require('node:assert/strict');
const { jstDateString, assetNames, renderBody, prepareWeekly } = require('./weekly-build.js');

test('jstDateString は JST の日付を返す', () => {
  // 火曜 15:00 UTC = 水曜 0:00 JST
  assert.equal(jstDateString(new Date('2026-11-03T15:00:00Z')), '20261104');
  assert.equal(jstDateString(new Date('2026-11-03T14:59:59Z')), '20261103');
});

test('assetNames は md5/sha1/sha256 を含む 8 ファイル', () => {
  const names = assetNames('t');
  assert.equal(names.length, 8);
  assert.ok(names.includes('t.tar.gz.checksum.md5'));
  assert.ok(names.includes('t.zip.checksum.sha256'));
});

const base = {
  serverUrl: 'https://github.com',
  repository: 'EC-CUBE/ec-cube2',
  tagName: 'eccube2-weekly-20261104',
  previousWeeklyTag: 'eccube2-weekly-20260929',
  latestReleaseTag: 'eccube-2.25.0',
  newBcBreaks: [],
  allBcBreaks: [],
  notes: '## What\'s Changed\n* `<textarea wrap="hard">` 属性削除する by @a in https://github.com/EC-CUBE/ec-cube2/pull/1294\n',
};

test('renderBody は引用符やバッククォートを含む Changelog をそのまま保持する', () => {
  const body = renderBody(base);
  assert.ok(body.includes('`<textarea wrap="hard">`'));
  assert.ok(body.includes('releases/download/eccube2-weekly-20261104/eccube2-weekly-20261104.zip.checksum.sha256'));
  assert.ok(!body.includes('[!WARNING]'));
  assert.ok(!body.includes('[!NOTE]'));
});

test('renderBody は新規の BC Break を WARNING で列挙する', () => {
  const body = renderBody({
    ...base,
    newBcBreaks: [{ number: 1437, title: 'SC_SendMail\n拡張', html_url: 'https://example.com/1437' }],
    allBcBreaks: [{ number: 1437, title: 'x', html_url: 'u' }],
  });
  assert.match(body, /> \[!WARNING\]\n> \*\*前回の Weekly build \(eccube2-weekly-20260929\) 以降に/);
  assert.ok(body.includes('> - SC_SendMail 拡張 #1437 (https://example.com/1437)'));
});

test('renderBody は累積の BC Break のみの場合 NOTE を出す', () => {
  const body = renderBody({ ...base, allBcBreaks: [{ number: 1, title: 'x', html_url: 'u' }] });
  assert.ok(body.includes('> [!NOTE]\n> eccube-2.25.0 以降に後方互換性のない変更 (BC Break) が 1 件'));
});

test('renderBody は上限を超える本文を切り詰める', () => {
  const body = renderBody({ ...base, notes: 'x'.repeat(200000) });
  assert.ok(body.length <= 120000);
  assert.ok(body.includes('/compare/eccube-2.25.0...eccube2-weekly-20261104'));
});

/**
 * Octokit のモック
 * commits: 履歴を祖先順に並べた SHA の配列 (単純な直線履歴を想定)
 */
function mockGithub({ commits, tags, releases, latest, bcPrs = [] }) {
  const idx = (sha) => commits.indexOf(sha);
  const rest = {
    repos: {
      getLatestRelease: async () => {
        if (!latest) {
          const e = new Error('Not Found');
          e.status = 404;
          throw e;
        }
        return { data: latest };
      },
      listReleases: async () => ({ data: releases }),
      getCommit: async ({ ref }) => ({ data: { sha: tags[ref] ?? ref } }),
      compareCommitsWithBasehead: async ({ basehead }) => {
        const [a, b] = basehead.split('...');
        const d = idx(b) - idx(a);
        return { data: { status: d === 0 ? 'identical' : d > 0 ? 'ahead' : 'behind' } };
      },
      generateReleaseNotes: async (args) => ({ data: { body: `notes since ${args.previous_tag_name}` } }),
    },
    search: { issuesAndPullRequests: 'search' },
    pulls: { get: async ({ pull_number }) => ({ data: bcPrs.find((p) => p.number === pull_number) }) },
  };
  return {
    rest,
    paginate: async (fn) => (fn === 'search' ? bcPrs.map((p) => ({ number: p.number })) : []),
  };
}

const context = (sha) => ({
  repo: { owner: 'EC-CUBE', repo: 'ec-cube2' },
  serverUrl: 'https://github.com',
  payload: { repository: { default_branch: 'master' } },
  sha,
});

const fixture = {
  commits: ['c0', 'c1', 'c2', 'c3'],
  tags: { 'eccube-2.25.0': 'c0', 'eccube2-weekly-20260929': 'c2' },
  latest: { tag_name: 'eccube-2.25.0' },
  releases: [
    { tag_name: 'eccube2-weekly-20261001', draft: true, prerelease: true },
    { tag_name: 'eccube2-weekly-20260929', draft: false, prerelease: true },
    { tag_name: 'eccube-2.25.0', draft: false, prerelease: false },
  ],
  bcPrs: [
    { number: 10, title: 'old', html_url: 'u10', merge_commit_sha: 'c1', merged_at: '1' },
    { number: 11, title: 'new', html_url: 'u11', merge_commit_sha: 'c3', merged_at: '3' },
  ],
};
const now = new Date('2026-11-03T15:30:00Z');

test('prepareWeekly は変更が無ければスキップする (draft は前回 weekly とみなさない)', async () => {
  const r = await prepareWeekly({ github: mockGithub(fixture), context: context('c2'), now });
  assert.equal(r.deploy, false);
  assert.match(r.reason, /eccube2-weekly-20260929/);
});

test('prepareWeekly は force でスキップしない', async () => {
  const r = await prepareWeekly({ github: mockGithub(fixture), context: context('c2'), now, inputs: { force: true } });
  assert.equal(r.deploy, true);
});

test('prepareWeekly は BC Break を前回 weekly 以降と累積に分ける', async () => {
  const r = await prepareWeekly({ github: mockGithub(fixture), context: context('c3'), now });
  assert.equal(r.deploy, true);
  assert.equal(r.tagName, 'eccube2-weekly-20261104');
  assert.equal(r.releaseName, 'eccube2-weekly-20261104 ⚠️ BC Break');
  assert.deepEqual(r.newBcBreaks.map((p) => p.number), [11]);
  assert.deepEqual(r.allBcBreaks.map((p) => p.number), [10, 11]);
  assert.ok(r.body.includes('notes since eccube-2.25.0'));
});

test('prepareWeekly はパス区切りや引用符を含むタグ名を拒否する', async () => {
  for (const tagName of ['foo/bar', 'a"b', '../x', 'a b']) {
    await assert.rejects(
      prepareWeekly({ github: mockGithub(fixture), context: context('c3'), now, inputs: { tagName } }),
      /タグ名の形式が不正/,
    );
  }
});

test('prepareWeekly は公開済みの同名タグがあればエラーにする', async () => {
  await assert.rejects(
    prepareWeekly({ github: mockGithub(fixture), context: context('c3'), now, inputs: { tagName: 'eccube2-weekly-20260929' } }),
    /既に公開済み/,
  );
});
