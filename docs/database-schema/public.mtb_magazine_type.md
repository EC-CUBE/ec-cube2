# public.mtb_magazine_type

## Description

メルマガ種別

## Columns

| Name | Type | Default | Nullable | Children | Parents | Comment |
| ---- | ---- | ------- | -------- | -------- | ------- | ------- |
| id | smallint |  | false |  |  | ID |
| name | text |  | true |  |  | 名称 |
| rank | smallint | 0 | false |  |  | 表示順 |

## Constraints

| Name | Type | Definition |
| ---- | ---- | ---------- |
| mtb_magazine_type_pkey | PRIMARY KEY | PRIMARY KEY (id) |

## Indexes

| Name | Definition |
| ---- | ---------- |
| mtb_magazine_type_pkey | CREATE UNIQUE INDEX mtb_magazine_type_pkey ON public.mtb_magazine_type USING btree (id) |

## Relations

![er](public.mtb_magazine_type.svg)

---

> Generated by [tbls](https://github.com/k1LoW/tbls)
