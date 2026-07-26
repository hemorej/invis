var app = new Vue({
    el: '#prod',
    data: {
        activeVariant: null,
        productVariant: null,
        submitting: false
    },
    mounted() {
        this.activeVariant = this.$refs.active.getAttribute('data-option-variant')
        this.productVariant = this.$refs.active.getAttribute('data-option-product')
    },
    methods: {
        makeActive: function(event){
            this.$refs.active.classList.remove('variant-on')
            this.$refs.active.classList.add('variant-off')
            event.target.classList.remove('variant-off')
            event.target.classList.add('variant-on')

            this.$refs.active = event.target
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
    }
});