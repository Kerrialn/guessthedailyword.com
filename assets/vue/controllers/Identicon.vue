<template>
  <Avatar :name="name" variant="beam" :size="size" />
</template>

<script>
import Avatar from "vue-boring-avatars";
export default {
  name: 'Identicon',
  props: {
    name: {
      type: String,
      required: true
    },
    size: {
      type: Int8Array,
      required: true
    },
  },
  components: {
    Avatar
  }
}
</script>
