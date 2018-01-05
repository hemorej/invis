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
        shipped: shipped
        paid: paid
    required: true