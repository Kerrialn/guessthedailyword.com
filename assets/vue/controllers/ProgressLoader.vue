<template>
  <div class="progress rounded-bottom-0" style="height: 5px" role="progressbar" aria-valuemin="0" aria-valuemax="100">
    <div class="progress-bar bg-dark" :style="{ width: progress + '%' }"></div>
  </div>
</template>

<script>
export default {
  name: 'ProgressLoader',
  props: {
    duration: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      progress: 0,
      interval: null
    };
  },
  mounted() {
    const totalMs = this.duration * 1000;
    const tickRate = 50; // update every 50ms
    const steps = totalMs / tickRate;
    let currentStep = 0;

    this.interval = setInterval(() => {
      currentStep++;
      this.progress = Math.min((currentStep / steps) * 100, 100);

      if (currentStep >= steps) {
        clearInterval(this.interval);
        this.$emit('finished');
      }
    }, tickRate);
  },
  beforeUnmount() {
    if (this.interval) clearInterval(this.interval);
  }
};
</script>

<style scoped>
.progress {
  height: 1.2rem;
  background-color: #e9ecef;
  border-radius: 0.375rem;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  background-color: #0d6efd;
  transition: width 0.05s linear;
}
</style>
