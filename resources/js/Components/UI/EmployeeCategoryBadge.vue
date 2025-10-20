<template>
  <span 
    :class="[
      'inline-flex items-center rounded-full text-xs font-medium border',
      sizeClasses,
      categoryClasses
    ]"
  >
    <component 
      :is="categoryIcon" 
      :class="iconSizeClasses"
      v-if="showIcon"
    />
    {{ categoryLabel }}
  </span>
</template>

<script setup>
import { computed } from 'vue'
import { 
  UserIcon,
  UserGroupIcon,
  AcademicCapIcon,
  BriefcaseIcon,
  Cog6ToothIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  category: {
    type: String,
    required: true,
    validator: value => ['worker', 'foreman', 'engineer', 'manager', 'system_admin'].includes(value)
  },
  showIcon: {
    type: Boolean,
    default: true
  },
  size: {
    type: String,
    default: 'sm',
    validator: value => ['xs', 'sm', 'md', 'lg'].includes(value)
  }
})

const categoryConfig = {
  worker: {
    label: 'İşçi',
    icon: UserIcon,
    classes: 'bg-blue-50 text-blue-700 border-blue-200'
  },
  foreman: {
    label: 'Forman',
    icon: UserGroupIcon,
    classes: 'bg-green-50 text-green-700 border-green-200'
  },
  engineer: {
    label: 'Mühendis',
    icon: AcademicCapIcon,
    classes: 'bg-purple-50 text-purple-700 border-purple-200'
  },
  manager: {
    label: 'Proje Yöneticisi',
    icon: BriefcaseIcon,
    classes: 'bg-orange-50 text-orange-700 border-orange-200'
  },
  system_admin: {
    label: 'Sistem Yöneticisi',
    icon: Cog6ToothIcon,
    classes: 'bg-red-50 text-red-700 border-red-200'
  }
}

const categoryLabel = computed(() => {
  return categoryConfig[props.category]?.label || 'Bilinmeyen'
})

const categoryIcon = computed(() => {
  return categoryConfig[props.category]?.icon || UserIcon
})

const categoryClasses = computed(() => {
  return categoryConfig[props.category]?.classes || 'bg-gray-50 text-gray-700 border-gray-200'
})

const sizeClasses = computed(() => {
  const sizes = {
    xs: 'px-1.5 py-0.5 text-xs',
    sm: 'px-2 py-1 text-xs',
    md: 'px-2.5 py-1 text-sm',
    lg: 'px-3 py-1.5 text-base'
  }
  return sizes[props.size]
})

const iconSizeClasses = computed(() => {
  const iconSizes = {
    xs: 'w-3 h-3 mr-1',
    sm: 'w-3 h-3 mr-1.5',
    md: 'w-4 h-4 mr-2',
    lg: 'w-5 h-5 mr-2'
  }
  return iconSizes[props.size]
})
</script>