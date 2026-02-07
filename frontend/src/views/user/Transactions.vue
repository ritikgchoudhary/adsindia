<template>
  <DashboardLayout page-title="Transactions">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="show-filter mb-3 text-end">
          <button type="button" class="btn btn--base showFilterBtn btn--sm" @click="toggleFilter">
            <i class="las la-filter"></i> Filter
          </button>
        </div>
        <div class="card custom--card responsive-filter-card mb-4" v-show="showFilter">
          <div class="card-body">
            <form @submit.prevent="handleFilter">
              <div class="d-flex flex-wrap gap-4">
                <div class="flex-grow-1">
                  <label class="form--label">Transaction Number</label>
                  <input type="search" v-model="filters.search" name="search" class="form-control form--control">
                </div>
                <div class="flex-grow-1 select2-parent">
                  <label class="form--label d-block">Type</label>
                  <select v-model="filters.trx_type" name="trx_type" class="form-select form--control select2" data-minimum-results-for-search="-1">
                    <option value="">All</option>
                    <option value="+">Plus</option>
                    <option value="-">Minus</option>
                  </select>
                </div>
                <div class="flex-grow-1 select2-parent">
                  <label class="form--label d-block">Remark</label>
                  <select v-model="filters.remark" class="form-select form--control select2" data-minimum-results-for-search="-1" name="remark">
                    <option value="">All</option>
                    <option v-for="remark in remarks" :key="remark" :value="remark">{{ formatRemark(remark) }}</option>
                  </select>
                </div>
                <div class="flex-grow-1 align-self-end">
                  <button type="submit" class="btn btn--base w-100">
                    <i class="las la-filter"></i> Filter
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <div class="campaign-table">
          <div class="card custom--card">
            <div class="card-body">
              <table class="table table--responsive--lg">
                <thead>
                  <tr>
                    <th>Trx</th>
                    <th>Transacted</th>
                    <th>Amount</th>
                    <th>Post Balance</th>
                    <th>Detail</th>
                  </tr>
                </thead>
                <tbody>
                  <template v-for="trx in transactions" :key="trx?.id || Math.random()">
                    <tr v-if="trx && trx.id">
                    <td>
                      <div><strong>{{ trx.trx }}</strong></div>
                    </td>
                    <td>
                      <div>
                        {{ formatDateTime(trx.created_at) }}<br>
                        <small class="text-muted">{{ trx.created_at_human }}</small>
                      </div>
                    </td>
                    <td>
                      <div>
                        <span class="fw-bold" :class="trx.trx_type === '+' ? 'text--success' : 'text--danger'">
                          {{ trx.trx_type }} {{ formatAmount(trx.amount) }}
                        </span>
                      </div>
                    </td>
                    <td>
                      <div>{{ formatAmount(trx.post_balance) }}</div>
                    </td>
                    <td>
                      <div>{{ trx.details }}</div>
                    </td>
                    </tr>
                  </template>
                  <tr v-if="transactions.length === 0">
                    <td colspan="5" class="text-center text-muted">Data not found</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../../components/DashboardLayout.vue'
import { userService } from '../../services/userService'

export default {
  name: 'Transactions',
  components: {
    DashboardLayout
  },
  setup() {
    const transactions = ref([])
    const remarks = ref([])
    const showFilter = ref(false)
    const filters = ref({
      search: '',
      trx_type: '',
      remark: ''
    })

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const formatDateTime = (dateString) => {
      if (!dateString) return '-'
      const date = new Date(dateString)
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      const hour12 = date.getHours() % 12 || 12
      const minutes = String(date.getMinutes()).padStart(2, '0')
      const ampm = date.getHours() >= 12 ? 'PM' : 'AM'
      return `${year}-${month}-${day} ${hour12}:${minutes} ${ampm}`
    }

    const formatRemark = (remark) => {
      return remark.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
    }

    const toggleFilter = () => {
      showFilter.value = !showFilter.value
    }

    const handleFilter = () => {
      fetchTransactions()
    }

    const fetchTransactions = async () => {
      try {
        const response = await userService.getTransactions(filters.value)
        if (response.status === 'success') {
          transactions.value = response.data?.data || []
          if (response.data?.remarks) {
            remarks.value = response.data.remarks
          }
        }
      } catch (error) {
        console.error('Error loading transactions:', error)
      }
    }

    onMounted(() => {
      fetchTransactions()
      // Initialize select2
      if (window.jQuery) {
        setTimeout(() => {
          window.jQuery('.select2').select2()
        }, 100)
      }
    })

    return {
      transactions,
      remarks,
      showFilter,
      filters,
      formatAmount,
      formatDateTime,
      formatRemark,
      toggleFilter,
      handleFilter
    }
  }
}
</script>

<style scoped>
.select2+.select2-container .select2-selection__rendered {
  line-height: unset;
}

.select2-container--default .select2-selection--single {
  border-width: 1px !important;
  border-radius: 12px !important;
  border-color: var(--select2-border) !important;
}

.select2-container--open .select2-selection.select2-selection--single,
.select2-container--open .select2-selection.select2-selection--multiple {
  border-radius: 12px !important;
}

.select2-container--default .select2-selection--single {
  padding: 16px 24px !important;
}

.select2+.select2-container .select2-selection__rendered {
  padding-right: 0px !important;
  padding-left: 0px !important;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
  top: 28px !important;
}

.select2-results__option.select2-results__option--selected,
.select2-results__option--selectable,
.select2-container--default .select2-results__option--disabled {
  border-bottom-color: hsl(var(--border-color)) !important;
}

.select2-results__option.select2-results__option--selected,
.select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
  color: hsl(var(--white)) !important;
  background-color: hsl(var(--base)) !important;
}

.select2-results__option.select2-results__option--selected,
.select2-results__option--selectable,
.select2-container--default .select2-results__option--disabled {
  border-bottom-color: hsl(var(--white)/0.2) !important;
}

.select2-container--default.select2-container--focus .select2-selection--single {
  outline: none !important;
  box-shadow: none !important;
  border-color: hsl(var(--base)) !important;
}
</style>
