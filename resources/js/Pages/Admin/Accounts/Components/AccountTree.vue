<template>
  <ul class="account-tree list-unstyled">
    <TreeNode v-for="node in nodes"
              :key="node.id"
              :node="node"
              :depth="0"
              @select="emit('select', $event)" />
  </ul>
</template>

<script setup>
import { defineComponent } from 'vue'

const props = defineProps({
  nodes: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['select'])

const TreeNode = defineComponent({
  name: 'TreeNode',
  props: {
    node: {
      type: Object,
      required: true
    },
    depth: {
      type: Number,
      default: 0
    }
  },
  emits: ['select'],
  computed: {
    hasChildren() {
      return Array.isArray(this.node.children) && this.node.children.length > 0
    },
    indentStyle() {
      return {
        paddingLeft: `${this.depth * 16}px`
      }
    },
    isExpanded() {
      return this.node._expanded ?? true
    }
  },
  methods: {
    toggle() {
      this.node._expanded = !this.isExpanded
    },
    selectNode() {
      this.$emit('select', this.node)
    }
  },
  template: `
    <li>
      <div class="tree-node d-flex align-items-center" :style="indentStyle">
        <button v-if="hasChildren"
                type="button"
                class="btn btn-link btn-sm p-0 me-2"
                @click="toggle">
          <i class="bi" :class="isExpanded ? 'bi-dash-square' : 'bi-plus-square'"></i>
        </button>
        <button type="button"
                class="btn btn-sm"
                :class="node.selected ? 'btn-primary' : 'btn-outline-secondary'"
                @click="selectNode">
          {{ node.account_code }} - {{ node.account_name }}
        </button>
      </div>
      <transition name="fade">
        <ul v-if="hasChildren && isExpanded" class="list-unstyled mb-0">
          <TreeNode v-for="child in node.children"
                    :key="child.id"
                    :node="child"
                    :depth="depth + 1"
                    @select="$emit('select', $event)" />
        </ul>
      </transition>
    </li>
  `
})
</script>

<style scoped>
.tree-node button.btn-link {
  color: #0d6efd;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
