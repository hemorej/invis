document.addEventListener('alpine:init', function () {
    Alpine.data('product', () => ({
        activeVariant: null,
        productVariant: null,
        submitting: false,

        init() {
            var active = this.$root.querySelector('.variant-on')
            if (!active) return
            this.activeVariant = active.getAttribute('data-option-variant')
            this.productVariant = active.getAttribute('data-option-product')
        },
        makeActive: function(event){
            var active = this.$root.querySelector('.variant-on')
            if (active) {
                active.classList.remove('variant-on')
                active.classList.add('variant-off')
            }
            event.target.classList.remove('variant-off')
            event.target.classList.add('variant-on')

            this.activeVariant = event.target.getAttribute('data-option-variant')
            this.productVariant = event.target.getAttribute('data-option-product')
        },
        addToCart: function(event){
            this.submitting = true

            axios.post('/prints/cart', {
                uri: this.$refs.uri.value,
                variant: this.activeVariant,
                action: 'add',
                csrf: this.$refs.csrf.value
              })
              .then(function (response) {
                document.location.replace('cart');
              })
        }
    }))
})
