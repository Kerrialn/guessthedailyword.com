<template>
  <div>


    <div v-if="error" class="alert text-center alert-danger" role="alert">
      {{ message }}
    </div>
    <ProgressLoader v-if="loader" :duration="loaderDuration" class="my-3"></ProgressLoader>

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
      {{ hint }}
    </div>

    <div class="d-flex gap-3 justify-content-center">
      <input
          v-for="(char, idx) in letters"
          :key="idx"
          ref="inputs"
          v-model="letters[idx]"
          maxlength="1"
          class="form-control"
          :class="getLetterClass(idx)"
          @keydown="handleKeydown($event, idx)"
      />
    </div>

    <a
        @click="submitGuess"
        class="btn btn-primary w-100 mt-4 btn-lg"
        :class="{ disabled: !isGuessComplete }"
        :aria-disabled="!isGuessComplete"
    >
      Submit
    </a>
  </div>
</template>

<script>
import ProgressLoader from "./ProgressLoader.vue";

export default {
  components: {ProgressLoader},
  props: {
    hint: {type: String, required: true},
    wordLength: {type: Int8Array, required: true},
    path: {type: String, required: true},
    previousGuesses: {type: Array, default: () => []}
  },
  data() {
    return {
      letters: Array.from({length: this.wordLength}, () => ''),
      error: false,
      loader: false,
      loaderDuration: null,
      message: null,
      submittedGuesses: [...this.previousGuesses],
      activeFeedback: Array.from({length: this.wordLength}, () => null),
    };
  },
  mounted() {
    this.$refs.inputs[0].focus();
  },
  computed: {
    isGuessComplete() {
      return this.letters.every(letter => letter.length === 1);
    }
  },
  methods: {


    handleKeydown(event, idx) {
      const key = event.key.toUpperCase();

      if (key.match(/^[A-Z]$/)) {
        event.preventDefault(); // prevent double-entry
        this.letters[idx] = key;

        // move to next input
        if (idx < this.letters.length - 1) {
          this.$nextTick(() => {
            this.$refs.inputs[idx + 1].focus();
            this.$refs.inputs[idx + 1].select();
          });
        }
      } else if (['Backspace', 'Delete'].includes(event.key)) {
        event.preventDefault();
        this.letters[idx] = '';
        if (idx > 0) {
          this.$nextTick(() => this.$refs.inputs[idx - 1].focus());
        }
      } else if (event.key === 'ArrowRight' && idx < this.letters.length - 1) {
        event.preventDefault();
        this.$refs.inputs[idx + 1].focus();
      } else if (event.key === 'ArrowLeft' && idx > 0) {
        event.preventDefault();
        this.$refs.inputs[idx - 1].focus();
      }
    },

    getLetterClass(idx) {
      const fb = this.activeFeedback[idx];

      return {
        'bg-success text-white': fb === 'correct',
        'bg-warning text-dark': fb === 'present',
        'bg-light': fb === 'absent',
        'is-invalid': this.error && !fb
      };
    },

    clearForm() {
      this.letters = Array.from({length: this.wordLength}, () => '');
      this.activeFeedback = Array.from({length: this.wordLength}, () => null);
      this.error = false;
      this.message = null;
      this.$nextTick(() => this.$refs.inputs[0].focus());
    },

    async submitGuess() {
      this.error = false;
      this.message = '';

      const guess = this.letters.join('').toLowerCase();
      let response;

      try {
        response = await fetch(this.path, {
          method: "POST",
          headers: {"Content-Type": "application/json"},
          body: JSON.stringify({content: guess}),
        });
      } catch (networkErr) {
        this.error = true;
        this.message = networkErr.message || 'Network error';
        return;
      }

      // 2) HTTP errors (4xx/5xx)
      if (!response.ok) {
        let errMsg = response.statusText;
        try {
          const errData = await response.json();
          errMsg = errData.message || errData.error || errMsg;
        } catch {

        }
        this.error = true;
        this.message = errMsg;

        this.loader = true;
        this.loaderDuration = 2;
        setTimeout(() => {
          this.clearForm();
          this.loader = false;
        }, 2000);

        return;
      }

      // 3) Parse the success payload
      let data;
      try {
        data = await response.json();
      } catch {
        this.error = true;
        this.message = 'Invalid JSON response';
        return;
      }

      const {isCorrect, feedback} = data;

      // 4) Update local state
      this.submittedGuesses.push({
        content: guess,
        isCorrect: isCorrect,
        feedback: feedback,
      });

      this.activeFeedback = feedback || [];

      if (isCorrect) {
        window.location.href = '/leaderboard';
      } else {
        this.error = true;
        if (this.submittedGuesses.length < 3) {
          this.message = 'Sorry, that\'s wrong, try again!';
          this.loader = true;
          this.loaderDuration = 2;
          setTimeout(() => {
            this.clearForm();
            this.loader = false;
          }, 2000);
        } else {
          this.message = 'Awww! game over, try again tomorrow';
          this.loader = true;
          this.loaderDuration = 2;
          setTimeout(() => {
            window.location.href = '/game-over';
          }, 2000);
        }
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

.bg-success {
  background-color: #28a745 !important;
}

.bg-warning {
  background-color: #ffc107 !important;
}

.bg-light {
  background-color: #e2e3e5 !important;
}
</style>
