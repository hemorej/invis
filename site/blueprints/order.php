title: Order
pages: false
files: false
options:
  delete: false
  status: false
fields:
  txn_id:
    label: txn_id
    type: text
    width: 1/3
    readonly: true
  txn_date:
    label: txn_date
    type: date
    format: MM/DD/YYYY
    readonly: true
    width: 1/3
  status:
    label: status
    width: 1/3
    type: select
    options:
        pending: pending
        paid: paid
        shipped: shipped
        canceled: canceled
        returned: returned
    required: true
  order_id:
    label: order id
    type: text
    readonly: true
  products:
    label: products
    readonly: true
    type: structure
    entry: >
      <a href="/{{uri}}" target="_blank">{{name}} ({{variant}})</a><br />
      sku: {{sku}}<br />
      qty: {{quantity}} total: {{amount}}
    fields:
      uri:
        label: url
        type: url
      name:
        label: name
        type: text
      variant:
        label: variant
        type: text
      sku:
        label: sku
        type: text
      quantity:
        label: quantity
        type: int
      amount:
        label: amount
        type: int
  customer:
    label: customer
    readonly: true
    type: textarea