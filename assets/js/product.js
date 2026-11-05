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

            fetch('/prints/cart', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    uri: this.$refs.uri.value,
                    variant: this.activeVariant,
                    action: 'add',
                    csrf: this.$refs.csrf.value
                })
              })
              .then(response => {
                if (!response.ok) throw new Error('HTTP ' + response.status)
                document.location.replace('cart');
              })
              .catch(() => {
                this.submitting = false
              })
        }
    }))
})
