title: Product
pages: false
files: true
fields:
  title:
    label: Title
    type: text
    help: The title of your product.
    required: true
  published:
    label: Published
    type: date
    help: Publishing date (December 21, 2017).
    required: true
    default: today
  description:
    label: Description
    type: textarea
  variants:
    label: Variants
    type: snippetfield
    snippet: variant
    fields:
      name:
        label: Variant name
        type:  text
        help: Describe variant
        required: true
      price:
        label: Price
        type:  text
        width: 1/2
        help: Numbers only
        required: true
      stock:
        label: Quantity in stock
        type: text
        validate: integer
        width: 1/2
        help: Leave blank for unlimited stock