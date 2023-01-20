<template>
  <k-field 
    class="k-autoranduuid-field" 
    :readonly="true"
    :help="help"
    :label="label"
    :required="required"
    >
    <k-input 
      theme="field" 
      type="text" 
      name="textfield" 
      :value="autoranduuid"
      @input="onInput">
  </k-field>
</template>


<script>
export default {
    props: {
      help: String,
      label: String,
      required: Boolean,
      autoranduuid: String,
    },
    methods: {
      onInput(autoranduuid) {
        this.$emit("input", autoranduuid);
      }
    }
}
</script>
