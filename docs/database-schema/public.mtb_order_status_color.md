# public.mtb_order_status_color

## Description

受注ステータス色

## Columns

| Name | Type | Default | Nullable | Children | Parents | Comment |
| ---- | ---- | ------- | -------- | -------- | ------- | ------- |
| id | smallint |  | false | [public.dtb_order](public.dtb_order.md) [public.dtb_order_temp](public.dtb_order_temp.md) |  | ID |
| name | text |  | true |  |  | 名称 |
| rank | smallint | 0 | false |  |  | 表示順 |

## Constraints

| Name | Type | Definition |
| ---- | ---- | ---------- |
| mtb_order_status_color_pkey | PRIMARY KEY | PRIMARY KEY (id) |

## Indexes

| Name | Definition |
| ---- | ---------- |
| mtb_order_status_color_pkey | CREATE UNIQUE INDEX mtb_order_status_color_pkey ON public.mtb_order_status_color USING btree (id) |

## Relations

![er](public.mtb_order_status_color.svg)

---

> Generated by [tbls](https://github.com/k1LoW/tbls)