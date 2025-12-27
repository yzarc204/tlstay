<template>
  <div class="pdf-viewer-wrapper">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center h-96">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent mb-4"></div>
        <p class="text-gray-600 mb-2">Đang tải hợp đồng...</p>
        <p class="text-xs text-gray-500">Lần đầu tiên có thể mất vài giây để tạo hợp đồng</p>
        <div class="mt-4 w-64 bg-gray-200 rounded-full h-2 mx-auto">
          <div class="bg-primary h-2 rounded-full animate-pulse" style="width: 60%"></div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="p-8 text-center">
      <ExclamationCircleIcon class="w-12 h-12 text-red-500 mx-auto mb-4" />
      <p class="text-red-600 mb-4">{{ error }}</p>
      <div class="flex gap-3 justify-center">
        <button
          @click="loadPdf"
          class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary transition-colors"
        >
          Thử lại
        </button>
        <a
          :href="pdfUrl"
          target="_blank"
          class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
        >
          Tải PDF
        </a>
      </div>
    </div>

    <!-- PDF Viewer -->
    <div v-else class="pdf-container bg-gray-100 rounded-lg overflow-hidden">
      <!-- PDF Controls -->
      <div v-if="showControls" class="bg-white border-b border-gray-200 px-4 py-2 flex items-center justify-between flex-wrap gap-2">
        <div class="flex items-center gap-2">
          <span class="text-sm text-gray-600">
            Tổng số trang: {{ totalPages || '...' }}
          </span>
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="zoomOut"
            :disabled="scale <= 0.5"
            class="px-3 py-1 text-sm text-gray-700 hover:bg-gray-100 rounded disabled:opacity-50"
          >
            −
          </button>
          <span class="text-sm text-gray-600 min-w-[60px] text-center">
            {{ Math.round(scale * 100) }}%
          </span>
          <button
            @click="zoomIn"
            :disabled="scale >= 2"
            class="px-3 py-1 text-sm text-gray-700 hover:bg-gray-100 rounded disabled:opacity-50"
          >
            +
          </button>
        </div>
        <a
          :href="pdfUrl"
          target="_blank"
          class="text-sm text-primary hover:text-secondary font-medium flex items-center"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Mở trong tab mới
        </a>
      </div>

      <!-- PDF Content - All Pages -->
      <div class="pdf-content overflow-auto" :style="{ height: height + 'px', maxHeight: maxHeight }">
        <div class="flex flex-col items-center p-4 gap-4">
          <canvas
            v-for="(page, index) in pages"
            :key="`page-${page.pageNum}`"
            :ref="el => setCanvasRef(el, index)"
            class="pdf-canvas shadow-lg bg-white"
            :style="{ transform: `scale(${scale})`, transformOrigin: 'top center' }"
          ></canvas>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import * as pdfjsLib from 'pdfjs-dist'
import { ExclamationCircleIcon } from '@heroicons/vue/24/outline'

// Set worker source for pdf.js
// Use local worker file from public folder (copied from node_modules)
if (typeof window !== 'undefined') {
  pdfjsLib.GlobalWorkerOptions.workerSrc = '/pdf.worker.min.js'
}

const props = defineProps({
  pdfUrl: {
    type: String,
    required: true,
  },
  height: {
    type: Number,
    default: 800,
  },
  maxHeight: {
    type: String,
    default: '800px',
  },
  showControls: {
    type: Boolean,
    default: true,
  },
  initialPage: {
    type: Number,
    default: 1,
  },
  initialScale: {
    type: Number,
    default: 1.0,
  },
})

const emit = defineEmits(['loaded', 'error', 'page-change'])

const loading = ref(true)
const error = ref('')
const totalPages = ref(0)
const scale = ref(props.initialScale)
const pages = ref([])
const canvasRefs = ref([])
let pdfDoc = null
let renderTasks = []

let timeoutId = null

const loadPdf = async () => {
  try {
    loading.value = true
    error.value = ''
    
    // Load PDF document
    const loadingTask = pdfjsLib.getDocument({
      url: props.pdfUrl,
      withCredentials: false,
    })
    
    pdfDoc = await loadingTask.promise
    totalPages.value = pdfDoc.numPages
    
    // Initialize pages array
    pages.value = Array.from({ length: totalPages.value }, (_, i) => ({
      pageNum: i + 1,
      rendered: false,
      rendering: false,
    }))
    
    // Initialize canvas refs array
    canvasRefs.value = new Array(totalPages.value).fill(null)
    
    // Wait for Vue to render the canvas elements
    await nextTick()
    await nextTick() // Double nextTick to ensure DOM is ready
    
    // Render all pages
    await renderAllPages()
    
    loading.value = false
    emit('loaded', { numPages: pdfDoc.numPages })
  } catch (err) {
    console.error('PDF loading error:', err)
    loading.value = false
    error.value = 'Không thể tải hợp đồng. Vui lòng thử lại hoặc tải PDF để xem.'
    emit('error', err)
  }
}

const setCanvasRef = (el, index) => {
  if (el) {
    canvasRefs.value[index] = el
    // Try to render this page if PDF is loaded and canvas is ready
    if (pdfDoc && pages.value[index] && !pages.value[index].rendered && !pages.value[index].rendering) {
      renderPage(index)
    }
  }
}

const renderAllPages = async () => {
  if (!pdfDoc) return
  
  try {
    // Cancel all previous render tasks
    renderTasks.forEach(task => {
      if (task) {
        task.cancel()
      }
    })
    renderTasks = []
    
    // Wait for all canvases to be mounted
    await nextTick()
    await new Promise(resolve => setTimeout(resolve, 200))
    
    // Check if all canvases are ready
    const allCanvasesReady = canvasRefs.value.every(canvas => canvas !== null)
    
    if (!allCanvasesReady) {
      // Wait a bit more
      await new Promise(resolve => setTimeout(resolve, 300))
    }
    
    // Render all pages sequentially to avoid conflicts
    for (let i = 0; i < pages.value.length; i++) {
      const canvas = canvasRefs.value[i]
      if (!canvas || pages.value[i].rendered || pages.value[i].rendering) continue
      
      await renderPage(i)
    }
  } catch (err) {
    if (err.name !== 'RenderingCancelledException') {
      console.error('PDF rendering error:', err)
      error.value = 'Không thể hiển thị trang PDF. Vui lòng thử lại.'
      emit('error', err)
    }
  }
}

const renderPage = async (index) => {
  if (!pdfDoc || !pages.value[index] || !canvasRefs.value[index]) return
  
  const pageInfo = pages.value[index]
  const canvas = canvasRefs.value[index]
  
  // Prevent double rendering
  if (pageInfo.rendered || pageInfo.rendering) return
  
  try {
    pageInfo.rendering = true
    const pageNum = pageInfo.pageNum
    
    // Get page
    const pdfPage = await pdfDoc.getPage(pageNum)
    
    // Calculate scale to fit canvas width
    const viewport = pdfPage.getViewport({ scale: 1.0 })
    const context = canvas.getContext('2d')
    
    // Set canvas dimensions
    const desiredWidth = 800 // Base width
    const displayScale = desiredWidth / viewport.width
    const scaledViewport = pdfPage.getViewport({ scale: displayScale })
    
    // Clear canvas first
    canvas.width = scaledViewport.width
    canvas.height = scaledViewport.height
    
    // Render PDF page
    const renderContext = {
      canvasContext: context,
      viewport: scaledViewport,
    }
    
    const renderTask = pdfPage.render(renderContext)
    renderTasks.push(renderTask)
    
    await renderTask.promise
    
    pageInfo.rendered = true
    pageInfo.rendering = false
    emit('page-change', pageNum)
  } catch (err) {
    pageInfo.rendering = false
    if (err.name !== 'RenderingCancelledException') {
      console.error('PDF rendering error for page', pageNum, ':', err)
      // Don't set error for individual page failures, just log it
    }
  }
}

const zoomIn = () => {
  if (scale.value < 2) {
    scale.value = Math.min(scale.value + 0.25, 2)
  }
}

const zoomOut = () => {
  if (scale.value > 0.5) {
    scale.value = Math.max(scale.value - 0.25, 0.5)
  }
}

// Watch for URL changes and load immediately
watch(() => props.pdfUrl, (newUrl) => {
  if (newUrl) {
    loadPdf()
  }
}, { immediate: true })

// Watch for scale changes
watch(() => scale.value, () => {
  // Scale is handled via CSS transform, no need to re-render
})

onMounted(() => {
  // Set timeout to hide loading if PDF takes too long
  timeoutId = setTimeout(() => {
    if (loading.value) {
      loading.value = false
      error.value = 'Hợp đồng đang được tạo, vui lòng đợi thêm một chút hoặc tải PDF để xem.'
    }
  }, 20000) // 20 seconds timeout
})

onUnmounted(() => {
  if (timeoutId) {
    clearTimeout(timeoutId)
  }
  // Cancel all ongoing render tasks
  renderTasks.forEach(task => {
    if (task) {
      task.cancel()
    }
  })
})
</script>

<style scoped>
.pdf-viewer-wrapper {
  width: 100%;
}

.pdf-container {
  border: 2px solid #e5e7eb;
}

.pdf-content {
  background: #f9fafb;
}

.pdf-canvas {
  display: block;
  margin: 0 auto;
  border: 1px solid #e5e7eb;
}
</style>
