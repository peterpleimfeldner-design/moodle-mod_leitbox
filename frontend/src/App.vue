<template>
  <div class="min-h-[600px] w-full bg-slate-50 font-sans text-slate-900 py-10 px-4 rounded-3xl" style="font-family: 'Inter', system-ui, sans-serif;">
    <Dashboard v-if="!sessionActive" @start-session="handleStartSession" />
    
    <div v-else class="max-w-2xl mx-auto">
      <div class="flex flex-col sm:flex-row justify-between items-center mb-10 gap-4">
        <button @click="endSession" class="text-slate-500 hover:text-[#4F46E5] font-medium flex items-center gap-2 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
          {{ getString('backtodashboard') }}
        </button>
        <div class="text-sm font-bold px-4 py-1.5 rounded-full shadow-sm border flex items-center gap-2" :class="getBadgeClass(activeBox)">
          <span class="w-2 h-2 rounded-full" :class="getDotClass(activeBox)"></span>
          {{ getBoxName(activeBox) }} 
          <span class="opacity-40 mx-1 font-normal">|</span> 
          {{ getString('cardxofy_x') }} {{ currentCardIndex + 1 }} {{ getString('cardxofy_y') }} <span class="font-extrabold">{{ sessionCards.length }}</span>
        </div>
      </div>

      <div v-if="loading" class="flex flex-col items-center justify-center py-32 text-slate-500">
        <svg class="animate-spin h-10 w-10 text-indigo-500 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="font-medium text-lg">{{ getString('loadingcards') }}</p>
      </div>
      
      <div v-else-if="sessionCards.length === 0" class="text-center py-20 bg-white rounded-3xl shadow-md border border-slate-100 p-10 transform scale-105 transition-all">
        <div class="text-6xl mb-6">🎉</div>
        <h3 class="text-3xl font-extrabold text-slate-800 mb-3 tracking-tight">{{ getString('sessiondone') }}</h3>
        <p class="text-slate-500 mb-8 max-w-sm mx-auto">{{ getString('sessiondonedesc') }}</p>
        <button @click="endSession" class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-bold py-3 px-8 rounded-full shadow-lg hover:shadow-xl transition-all hover:-translate-y-0.5">
          {{ getString('backtodashboard') }}
        </button>
      </div>
      
      <div v-else class="flex flex-col items-center w-full perspective-1000">
        <Transition name="fade-slide" mode="out-in">
          <Card :key="currentCard.id" :card="currentCard" @rate="handleRate" />
        </Transition>
      </div>
      
      <!-- Progress Bar -->
      <div v-if="!loading && sessionCards.length > 0" class="mt-14 max-w-md mx-auto relative group">
        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden shadow-inner">
          <div class="bg-gradient-to-r from-indigo-400 to-violet-500 h-2 rounded-full transition-all duration-500 ease-out" :style="{ width: `${progressPercentage}%` }"></div>
        </div>
        <div class="absolute -top-6 text-center w-full text-xs font-bold text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity">
            {{ Math.round(progressPercentage) }}% {{ getString('completed') }}
        </div>
      </div>
      
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import Dashboard from './components/Dashboard.vue';
import Card from './components/Card.vue';
import { getCardsByBox, submitAnswer } from './api';

const getString = (key) => {
    if (window.M && window.M.str && window.M.str.mod_recall && window.M.str.mod_recall[key]) {
        return window.M.str.mod_recall[key];
    }
    return key;
};

const sessionActive = ref(false);
const activeBox = ref(null);
const loading = ref(false);
const sessionCards = ref([]);
const currentCardIndex = ref(0);

const currentCard = computed(() => sessionCards.value[currentCardIndex.value]);

const progressPercentage = computed(() => {
    if (sessionCards.value.length === 0) return 100;
    return (currentCardIndex.value / sessionCards.value.length) * 100;
});

const getBadgeClass = (box) => {
    const classes = [
        'bg-blue-50 text-blue-700 border-blue-200',
        'bg-orange-50 text-orange-800 border-orange-200',
        'bg-yellow-50 text-yellow-800 border-yellow-200',
        'bg-lime-50 text-lime-800 border-lime-200',
        'bg-emerald-50 text-emerald-800 border-emerald-200',
        'bg-green-100 text-green-800 border-green-300'
    ];
    return classes[box] || 'bg-slate-100 text-slate-700 border-slate-200';
};

const getDotClass = (box) => {
    const classes = ['bg-blue-500', 'bg-orange-500', 'bg-yellow-500', 'bg-lime-500', 'bg-emerald-500', 'bg-green-600'];
    return classes[box] || 'bg-slate-500';
};

const getBoxName = (box) => {
    return getString('box' + box);
};

const handleStartSession = async (boxnumber) => {
    activeBox.value = boxnumber;
    sessionActive.value = true;
    loading.value = true;
    currentCardIndex.value = 0;
    try {
        sessionCards.value = await getCardsByBox(boxnumber);
    } catch (e) {
        console.error("Error loading session:", e);
        alert(getString('error_loading_cards'));
        sessionActive.value = false;
    } finally {
        loading.value = false;
    }
};

const handleRate = async (rating) => {
    if (!currentCard.value) return;
    
    // Fire and forget answer submission to Moodle API
    submitAnswer(currentCard.value.id, rating).catch(e => {
        console.error("Failed to save answer for card", currentCard.value.id, e);
    });

    // Move to next card locally
    if (currentCardIndex.value < sessionCards.value.length - 1) {
        currentCardIndex.value++;
    } else {
        // Session complete
        currentCardIndex.value++; // triggers 100% progress momentarily
        setTimeout(() => {
            sessionCards.value = [];
        }, 500);
    }
};

const endSession = () => {
    sessionActive.value = false;
    sessionCards.value = [];
    activeBox.value = null;
};
</script>

<style>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
.fade-slide-enter-from {
  opacity: 0;
  transform: translateX(30px) rotateY(10deg);
}
.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(-30px) rotateY(-10deg);
}
.perspective-1000 {
  perspective: 1000px;
}
</style>
