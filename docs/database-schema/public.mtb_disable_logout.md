# public.mtb_disable_logout

## Description

ログアウト無効ページ

## Columns

| Name | Type | Default | Nullable | Children | Parents | Comment |
| ---- | ---- | ------- | -------- | -------- | ------- | ------- |
| id | smallint |  | false |  |  | ID |
| name | text |  | true |  |  | 名称 |
| rank | smallint | 0 | false |  |  | 表示順 |

## Constraints

| Name | Type | Definition |
| ---- | ---- | ---------- |
| mtb_disable_logout_pkey | PRIMARY KEY | PRIMARY KEY (id) |

## Indexes

| Name | Definition |
| ---- | ---------- |
| mtb_disable_logout_pkey | CREATE UNIQUE INDEX mtb_disable_logout_pkey ON public.mtb_disable_logout USING btree (id) |

## Relations

![er](public.mtb_disable_logout.svg)

---

> Generated by [tbls](https://github.com/k1LoW/tbls)
