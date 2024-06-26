# public.dtb_category_total_count

## Description

カテゴリ商品合計数情報

## Columns

| Name | Type | Default | Nullable | Children | Parents | Comment |
| ---- | ---- | ------- | -------- | -------- | ------- | ------- |
| category_id | integer |  | false |  | [public.dtb_category](public.dtb_category.md) | カテゴリID |
| product_count | integer |  | true |  |  | 商品合計数 |
| create_date | timestamp without time zone | CURRENT_TIMESTAMP | false |  |  | 作成日時 |

## Constraints

| Name | Type | Definition |
| ---- | ---- | ---------- |
| dtb_category_total_count_pkey | PRIMARY KEY | PRIMARY KEY (category_id) |

## Indexes

| Name | Definition |
| ---- | ---------- |
| dtb_category_total_count_pkey | CREATE UNIQUE INDEX dtb_category_total_count_pkey ON public.dtb_category_total_count USING btree (category_id) |

## Relations

![er](public.dtb_category_total_count.svg)

---

> Generated by [tbls](https://github.com/k1LoW/tbls)
