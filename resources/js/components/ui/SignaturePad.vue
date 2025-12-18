<template>
  <div class="signature-pad-container">
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ label }}
        <span v-if="required" class="text-red-500">*</span>
      </label>
      <p v-if="hint" class="text-xs text-gray-500 mb-2">{{ hint }}</p>
    </div>
    
    <div class="border-2 border-gray-300 rounded-lg bg-white relative" :class="{ 'border-red-500': hasError }">
      <canvas
        ref="canvasRef"
        :width="width"
        :height="height"
        class="cursor-crosshair touch-none"
        @mousedown="startDrawing"
        @mousemove="draw"
        @mouseup="stopDrawing"
        @mouseleave="stopDrawing"
        @touchstart="handleTouchStart"
        @touchmove="handleTouchMove"
        @touchend="stopDrawing"
      ></canvas>
      
      <div v-if="!hasSignature" class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <p class="text-gray-400 text-sm">Vẽ chữ ký của bạn ở đây</p>
      </div>
    </div>
    
    <div v-if="hasError" class="mt-1 text-sm text-red-600">{{ errorMessage }}</div>
    
    <div class="mt-3 flex gap-2">
      <button
        type="button"
        @click="clear"
        class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
        :disabled="!hasSignature"
      >
        Xóa
      </button>
      <button
        v-if="showPreview && hasSignature"
        type="button"
        @click="togglePreview"
        class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
      >
        {{ showPreviewImage ? 'Ẩn' : 'Xem' }} xem trước
      </button>
    </div>
    
    <div v-if="showPreviewImage && hasSignature" class="mt-3 p-3 bg-gray-50 rounded-lg border">
      <p class="text-xs text-gray-600 mb-2">Xem trước:</p>
      <div class="bg-white p-4 rounded border border-gray-300 inline-block">
        <img :src="signatureData" alt="Chữ ký" class="max-w-full h-auto" style="background: repeating-conic-gradient(#f0f0f0 0% 25%, white 0% 50%) 50% / 20px 20px;" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  width: {
    type: Number,
    default: 600,
  },
  height: {
    type: Number,
    default: 200,
  },
  label: {
    type: String,
    default: 'Chữ ký',
  },
  hint: {
    type: String,
    default: 'Vẽ chữ ký của bạn bằng chuột hoặc ngón tay',
  },
  required: {
    type: Boolean,
    default: false,
  },
  hasError: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: '',
  },
  showPreview: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['update:modelValue'])

const canvasRef = ref(null)
const isDrawing = ref(false)
const lastX = ref(0)
const lastY = ref(0)
const showPreviewImage = ref(false)

let ctx = null

const hasSignature = computed(() => {
  return !!props.modelValue
})

const signatureData = computed(() => {
  return props.modelValue
})

onMounted(() => {
  if (canvasRef.value) {
    ctx = canvasRef.value.getContext('2d', { alpha: true }) // Đảm bảo có alpha channel
    ctx.strokeStyle = '#000000'
    ctx.lineWidth = 2
    ctx.lineCap = 'round'
    ctx.lineJoin = 'round'
    
    // Đảm bảo nền trong suốt (clear canvas)
    ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height)
    
    // Load existing signature if provided
    if (props.modelValue) {
      loadSignature(props.modelValue)
    }
  }
})

watch(() => props.modelValue, (newValue) => {
  if (newValue && canvasRef.value && ctx) {
    loadSignature(newValue)
  } else if (!newValue && canvasRef.value && ctx) {
    clearCanvas()
  }
})

const loadSignature = (dataUrl) => {
  const img = new Image()
  img.onload = () => {
    if (ctx && canvasRef.value) {
      // Clear canvas với nền trong suốt
      ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height)
      
      // Tính toán vị trí để căn giữa chữ ký
      const x = (canvasRef.value.width - img.width) / 2
      const y = (canvasRef.value.height - img.height) / 2
      
      ctx.drawImage(img, x, y)
    }
  }
  img.src = dataUrl
}

const clearCanvas = () => {
  if (ctx && canvasRef.value) {
    ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height)
  }
}

const getEventPos = (e) => {
  const rect = canvasRef.value.getBoundingClientRect()
  const scaleX = canvasRef.value.width / rect.width
  const scaleY = canvasRef.value.height / rect.height
  
  if (e.touches && e.touches.length > 0) {
    return {
      x: (e.touches[0].clientX - rect.left) * scaleX,
      y: (e.touches[0].clientY - rect.top) * scaleY,
    }
  }
  
  return {
    x: (e.clientX - rect.left) * scaleX,
    y: (e.clientY - rect.top) * scaleY,
  }
}

const startDrawing = (e) => {
  e.preventDefault()
  isDrawing.value = true
  const pos = getEventPos(e)
  lastX.value = pos.x
  lastY.value = pos.y
}

const draw = (e) => {
  if (!isDrawing.value || !ctx) return
  
  e.preventDefault()
  const pos = getEventPos(e)
  
  ctx.beginPath()
  ctx.moveTo(lastX.value, lastY.value)
  ctx.lineTo(pos.x, pos.y)
  ctx.stroke()
  
  lastX.value = pos.x
  lastY.value = pos.y
}

const handleTouchStart = (e) => {
  e.preventDefault()
  startDrawing(e)
}

const handleTouchMove = (e) => {
  e.preventDefault()
  draw(e)
}

const stopDrawing = () => {
  if (isDrawing.value) {
    isDrawing.value = false
    saveSignature()
  }
}

// Hàm tìm bounding box của chữ ký (loại bỏ phần trắng thừa)
const getBoundingBox = (canvas) => {
  const ctx = canvas.getContext('2d')
  const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height)
  const data = imageData.data
  
  let minX = canvas.width
  let minY = canvas.height
  let maxX = 0
  let maxY = 0
  
  // Tìm vùng có pixel không trong suốt
  for (let y = 0; y < canvas.height; y++) {
    for (let x = 0; x < canvas.width; x++) {
      const index = (y * canvas.width + x) * 4
      const alpha = data[index + 3]
      
      if (alpha > 0) { // Pixel có màu (không trong suốt)
        minX = Math.min(minX, x)
        minY = Math.min(minY, y)
        maxX = Math.max(maxX, x)
        maxY = Math.max(maxY, y)
      }
    }
  }
  
  // Thêm padding nhỏ
  const padding = 10
  minX = Math.max(0, minX - padding)
  minY = Math.max(0, minY - padding)
  maxX = Math.min(canvas.width, maxX + padding)
  maxY = Math.min(canvas.height, maxY + padding)
  
  return {
    x: minX,
    y: minY,
    width: maxX - minX,
    height: maxY - minY
  }
}

const saveSignature = () => {
  if (!canvasRef.value) return
  
  const canvas = canvasRef.value
  const ctx = canvas.getContext('2d')
  
  // Kiểm tra xem có chữ ký không
  const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height)
  const data = imageData.data
  let hasContent = false
  
  for (let i = 3; i < data.length; i += 4) {
    if (data[i] > 0) { // Có pixel không trong suốt
      hasContent = true
      break
    }
  }
  
  if (!hasContent) {
    emit('update:modelValue', '')
    return
  }
  
  // Tìm bounding box của chữ ký
  const bbox = getBoundingBox(canvas)
  
  // Nếu không tìm thấy nội dung, return
  if (bbox.width <= 0 || bbox.height <= 0) {
    emit('update:modelValue', '')
    return
  }
  
  // Tạo canvas mới chỉ chứa phần chữ ký
  const croppedCanvas = document.createElement('canvas')
  croppedCanvas.width = bbox.width
  croppedCanvas.height = bbox.height
  const croppedCtx = croppedCanvas.getContext('2d')
  
  // Vẽ phần chữ ký lên canvas mới (nền trong suốt)
  croppedCtx.drawImage(
    canvas,
    bbox.x, bbox.y, bbox.width, bbox.height,
    0, 0, bbox.width, bbox.height
  )
  
  // Export với nền trong suốt
  const dataUrl = croppedCanvas.toDataURL('image/png')
  emit('update:modelValue', dataUrl)
}

const clear = () => {
  clearCanvas()
  emit('update:modelValue', '')
  showPreviewImage.value = false
}

const togglePreview = () => {
  showPreviewImage.value = !showPreviewImage.value
}
</script>

<style scoped>
.signature-pad-container {
  width: 100%;
}

canvas {
  display: block;
  width: 100%;
  height: auto;
  border-radius: 0.5rem;
}
</style>
