var app = new Vue({
    el: '#prod',
    data: {
        activeVariant: null,
        productVariant: null,
        submitting: false
    },
    components: {
        'carousel': VueCarousel.Carousel,
        'slide': VueCarousel.Slide
    },
    mounted() {
        this.activeVariant = this.$refs.active.getAttribute('data-option-variant')
        this.productVariant = this.$refs.active.getAttribute('data-option-product')
    },
    methods: {
        makeActive: function(event){
            this.$refs.active.classList.remove('bb', 'b--gold', 'bw2')
            event.target.classList.add('bb', 'b--gold', 'bw2')

            this.$refs.active = event.target
            this.activeVariant = event.target.getAttribute('data-option-variant')
            this.productVariant = event.target.getAttribute('data-option-product')
        },
        addToCart: function(event){
            this.submitting = true

            window.umami('add-cart-'+this.productVariant)
            .then(function (){
                axios.post('/prints/cart', {
                    uri: this.$refs.uri.value,
                    variant: this.activeVariant,
                    action: 'add',
                    csrf: this.$refs.csrf.value
                  })
                  .then(function (response) {
                    document.location.replace('cart');
                  })
              }.bind(this))
        }
    }
});