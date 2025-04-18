<template>
  <div class="d-flex justify-content-between align-items-center h3 mb-4">
    <div class="d-flex gap-1">
      <span
          v-for="(guess, idx) in submittedGuesses"
          :key="idx"
          :class="[
          'badge',
          guess.isCorrect ? 'text-success border border-3 border-success' : 'text-danger border border-3 border-danger',
          'rounded-pill'
        ]"
      >
        {{ guess.content }}
      </span>
    </div>
    <span class="badge rounded-pill text-bg-dark">{{ 3 - submittedGuesses.length }} left</span>
  </div>

  <div class="h5 mb-5 text-center">
    Hint: {{ hint }}
  </div>

  <div class="d-flex gap-3 justify-content-center">
    <input
        v-for="(char, idx) in letters"
        :key="idx"
        ref="inputs"
        v-model="letters[idx]"
        maxlength="1"
        class="form-control"
        :class="{ 'is-invalid': error }"
        @keydown="handleKeydown($event, idx)"
        @input="handleInput($event, idx)"
    />
  </div>

  <a
      @click="submitGuess"
      class="btn btn-primary w-100 mt-4 btn-lg"
  >
    Submit
  </a>
</template>
<script>
export default {
  props: {
    word: { type: String, required: true },
    hint: { type: String, required: true },
    path: { type: String, required: true },
    previousGuesses: { type: Array, default: () => [] }
  },
  mounted() {
    this.$refs.inputs[0].focus();
  },
  data() {
    return {
      letters: Array.from({ length: this.word.length }, () => ''),
      error: false,
      submittedGuesses: [...this.previousGuesses]
    };
  },
  methods: {
    handleInput(event, idx) {
      const input = event.target;
      let char = input.value.toUpperCase();

      if (char.match(/^[A-Z]$/)) {
        this.letters[idx] = char;

        // Focus next input if available
        if (idx < this.letters.length - 1) {
          this.$refs.inputs[idx + 1].focus();
          this.$refs.inputs[idx + 1].select();
        }
      } else {
        this.letters[idx] = '';
      }
      // Clear error state when typing
      this.error = false;
    },
    handleKeydown(event, idx) {
      if (event.key === 'Backspace' || event.key === 'Delete') {
        event.preventDefault();
        this.letters[idx] = '';
        if (idx > 0) {
          this.$refs.inputs[idx - 1].focus();
        }
      } else if (event.key === 'ArrowRight') {
        event.preventDefault();
        if (idx < this.letters.length - 1) {
          this.$refs.inputs[idx + 1].focus();
        }
      } else if (event.key === 'ArrowLeft') {
        event.preventDefault();
        if (idx > 0) {
          this.$refs.inputs[idx - 1].focus();
        }
      }
    },
    async submitGuess() {
      // If limit reached, reload the page
      const guess = this.letters.join('').toLowerCase();

      const response = await fetch(this.path, {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({ content: guess }),
      });

      const data = await response.json();

      this.submittedGuesses.push({
        content: guess,
        isCorrect: data.correct
      });

      // If guess is wrong, show error; if correct, reload the page.
      if (!data.correct) {
        this.error = true;
        if (this.submittedGuesses.length+1 >= 3) {
          window.location.reload();
        }
      } else {
        window.location.reload();
      }
    }
  }
};
</script>


<style scoped>
input {
  text-align: center;
  text-transform: uppercase;
  padding: 1em;
  background-color: #eee;
  border-radius: .35rem;
  font-weight: bold;
  border: 2px solid transparent;
}

input:focus {
  outline: none;
  box-shadow: none;
  border: 2px solid #aaa;
}

.is-invalid {
  border: 2px solid #dc3545 !important;
}
</style>
