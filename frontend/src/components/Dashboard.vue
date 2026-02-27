<template>
  <div class="max-w-5xl mx-auto p-6 lg:p-10">
    <header class="mb-12 text-center relative">
      <h2 class="text-4xl lg:text-5xl font-extrabold text-[#4F46E5] tracking-tight pb-2">{{ getString('dashboardtitle') }}</h2>
      <p class="text-slate-500 mt-3 text-lg">{{ getString('dashboardsbtitle') }}</p>
      
      <button @click="showInfo = true" class="mt-4 md:absolute md:top-0 md:right-0 md:mt-0 text-sm bg-indigo-50 hover:bg-indigo-100 text-indigo-600 px-4 py-2 rounded-full font-medium transition-colors flex items-center justify-center gap-2 mx-auto border border-indigo-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        {{ getString('howitworks') }}
      </button>
    </header>

    <div class="grid grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
      <div v-for="box in 6" :key="box" 
           @click="startSession(box - 1)"
           class="rounded-3xl p-8 flex flex-col items-center justify-center transition-all duration-300 group shadow-sm hover:shadow-md"
           :class="getBoxOuterClass(box - 1, counts[box-1])">
        
        <div class="text-5xl mb-4 group-hover:-translate-y-1 transition-transform duration-300">
          {{ getBoxEmoji(box-1) }}
        </div>
        
        <h3 class="font-extrabold text-slate-800 text-xl tracking-wide uppercase mb-4">{{ getBoxName(box - 1) }}</h3>
        
        <div class="flex items-center justify-center">
          <!-- Active Card Count Badge -->
          <div v-if="counts[box-1] > 0" 
               class="px-3 py-1 rounded-full text-sm font-bold flex items-center justify-center gap-1.5 shadow-sm border"
               :class="getBadgeClass(box-1)">
            <span class="w-2 h-2 rounded-full animate-pulse" :class="getBadgeDotClass(box-1)"></span>
            {{ counts[box-1] }} {{ getString('cards') }}
          </div>
          
          <!-- Empty Card Count Badge -->
          <div v-else-if="counts[box-1] === 0" 
               class="px-3 py-1 rounded-full text-sm font-medium border border-dashed border-slate-300 bg-slate-100 text-slate-500 flex items-center justify-center gap-1.5">
            0 {{ getString('cards') }}
          </div>
          
          <!-- Loading state -->
          <div v-else class="text-slate-400">
             <svg class="animate-spin h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
             </svg>
          </div>
        </div>

      </div>
    </div>

    <!-- Info Modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showInfo" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" @click="showInfo = false">
          <!-- Backdrop -->
          <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"></div>
          
          <!-- Modal Panel -->
          <div class="relative bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-8 sm:p-10 overflow-hidden transform transition-all" @click.stop>
            <button @click="showInfo = false" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 bg-slate-50 hover:bg-slate-100 p-2 rounded-full transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <h3 class="text-2xl font-extrabold text-slate-800 mb-6 flex items-center gap-3">
              <span class="bg-indigo-100 text-indigo-600 p-2 rounded-xl">🎓</span> {{ getString('systemtitle') }}
            </h3>
            
            <div class="space-y-6 text-slate-600">
              <p class="text-lg" v-html="getString('systemintro')"></p>
              
              <div class="bg-slate-50 border border-slate-100 rounded-2xl p-5 space-y-4">
                <div class="flex items-start gap-4">
                  <div class="mt-1 bg-emerald-100 text-emerald-700 p-1.5 rounded-lg shrink-0">🟢</div>
                  <div>
                    <h4 class="font-bold text-slate-800">{{ getString('known_btn') }}</h4>
                    <p class="text-sm mt-1" v-html="getString('known_desc')"></p>
                  </div>
                </div>
                
                <div class="flex items-start gap-4">
                  <div class="mt-1 bg-yellow-100 text-yellow-700 p-1.5 rounded-lg shrink-0">🟡</div>
                  <div>
                    <h4 class="font-bold text-slate-800">{{ getString('again_btn') }}</h4>
                    <p class="text-sm mt-1" v-html="getString('again_desc')"></p>
                  </div>
                </div>

                <div class="flex items-start gap-4">
                  <div class="mt-1 bg-red-100 text-red-700 p-1.5 rounded-lg shrink-0">🔴</div>
                  <div>
                    <h4 class="font-bold text-slate-800">{{ getString('hard_btn') }}</h4>
                    <p class="text-sm mt-1" v-html="getString('hard_desc')"></p>
                  </div>
                </div>
              </div>
              
              <p class="text-sm bg-indigo-50 text-indigo-800 p-4 rounded-xl border border-indigo-100" v-html="getString('systemtip')"></p>
            </div>
            
            <div class="mt-8">
              <button @click="showInfo = false" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 rounded-xl transition-colors">
                {{ getString('gotit') }}
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { getBoxCounts } from '../api';

const emit = defineEmits(['start-session']);

const counts = ref({});
const showInfo = ref(false);

const loadCounts = async () => {
    try {
        counts.value = await getBoxCounts();
    } catch (e) {
        console.error("Failed to load counts", e);
    }
};

const getString = (key) => {
    if (window.M && window.M.str && window.M.str.mod_recall && window.M.str.mod_recall[key]) {
        return window.M.str.mod_recall[key];
    }
    return key;
};

onMounted(() => {
    loadCounts();
});

const getBoxEmoji = (box) => {
    const emojis = ['🆕', '🚨', '📚', '🔄', '🧠', '🏆'];
    return emojis[box] || '📦';
};

const getBoxName = (box) => {
    return getString('box' + box);
};

const getBoxOuterClass = (box, count) => {
    if (count === 0) {
        return 'opacity-60 border-2 border-dashed border-slate-300 bg-slate-50 hover:opacity-80 pointer-events-none cursor-default';
    }
    const styles = [
        'bg-white border-l-4 border-l-blue-400 border border-slate-200 cursor-pointer hover:-translate-y-1',     // Box 0
        'bg-white border-l-4 border-l-orange-500 border border-slate-200 cursor-pointer hover:-translate-y-1',   // Box 1
        'bg-white border-l-4 border-l-yellow-400 border border-slate-200 cursor-pointer hover:-translate-y-1',   // Box 2
        'bg-white border-l-4 border-l-lime-400 border border-slate-200 cursor-pointer hover:-translate-y-1',     // Box 3
        'bg-white border-l-4 border-l-emerald-400 border border-slate-200 cursor-pointer hover:-translate-y-1',  // Box 4
        'bg-white border-l-4 border-l-green-500 border border-slate-200 cursor-pointer hover:-translate-y-1',    // Box 5
    ];
    return styles[box] || 'bg-white border border-slate-200 cursor-pointer hover:-translate-y-1';
};

const getBadgeClass = (box) => {
    const styles = [
        'bg-blue-50 text-blue-700 border-blue-200',
        'bg-orange-50 text-orange-800 border-orange-200',
        'bg-yellow-50 text-yellow-800 border-yellow-200',
        'bg-lime-50 text-lime-800 border-lime-200',
        'bg-emerald-50 text-emerald-800 border-emerald-200',
        'bg-green-100 text-green-800 border-green-300',
    ];
    return styles[box] || 'bg-slate-100 text-slate-600 border-slate-200';
};

const getBadgeDotClass = (box) => {
    const styles = ['bg-blue-500', 'bg-orange-500', 'bg-yellow-500', 'bg-lime-500', 'bg-emerald-500', 'bg-green-600'];
    return styles[box] || 'bg-slate-500';
};


const startSession = (boxnumber) => {
    if (counts.value[boxnumber] > 0) {
        emit('start-session', boxnumber);
    }
};
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95) translateY(20px);
  opacity: 0;
}
</style>
