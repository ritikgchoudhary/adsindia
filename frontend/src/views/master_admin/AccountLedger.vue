<template>
  <MasterAdminLayout page-title="Account Ledger">
    <div class="ma-account-ledger">

      <!-- Top Summary Cards -->
      <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-3 tw-gap-6 tw-mb-8">
        <!-- Today Profit -->
        <div class="tw-bg-gradient-to-br tw-from-emerald-900/50 tw-to-slate-900 tw-rounded-[32px] tw-p-8 tw-border tw-border-emerald-500/20 tw-shadow-[0_0_40px_-10px_rgba(16,185,129,0.15)] tw-relative tw-overflow-hidden tw-group tw-transition-all tw-duration-300 hover:-tw-translate-y-1 hover:tw-shadow-[0_0_40px_-5px_rgba(16,185,129,0.25)] hover:tw-border-emerald-500/40">
          <div class="tw-absolute -tw-right-10 -tw-top-10 tw-w-40 tw-h-40 tw-bg-emerald-500/10 tw-rounded-full tw-blur-3xl tw-transition-all tw-duration-300 group-hover:tw-bg-emerald-500/20"></div>
          <div class="tw-flex tw-justify-between tw-items-start tw-relative">
            <div>
              <div class="tw-inline-flex tw-items-center tw-gap-2 tw-bg-emerald-500/10 tw-text-emerald-400 tw-px-3 tw-py-1.5 tw-rounded-full tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-mb-4 tw-border tw-border-emerald-500/10">
                <i class="fas fa-calendar-day"></i> Today's Profit
              </div>
              <h3 class="tw-text-white tw-text-4xl tw-font-black tw-mb-1 tw-tracking-tight">₹{{ formatAmount(summary.today_profit) }}</h3>
              <p class="tw-text-slate-400 tw-text-xs tw-font-medium">Net profit generated today</p>
            </div>
            <div class="tw-w-14 tw-h-14 tw-rounded-2xl tw-bg-emerald-500/10 tw-text-emerald-400 tw-flex tw-items-center tw-justify-center tw-text-2xl tw-shadow-inner tw-border tw-border-emerald-500/20">
              <i class="fas fa-chart-line"></i>
            </div>
          </div>
        </div>

        <!-- Month Profit -->
        <div class="tw-bg-gradient-to-br tw-from-blue-900/50 tw-to-slate-900 tw-rounded-[32px] tw-p-8 tw-border tw-border-blue-500/20 tw-shadow-[0_0_40px_-10px_rgba(59,130,246,0.15)] tw-relative tw-overflow-hidden tw-group tw-transition-all tw-duration-300 hover:-tw-translate-y-1 hover:tw-shadow-[0_0_40px_-5px_rgba(59,130,246,0.25)] hover:tw-border-blue-500/40">
          <div class="tw-absolute -tw-right-10 -tw-top-10 tw-w-40 tw-h-40 tw-bg-blue-500/10 tw-rounded-full tw-blur-3xl tw-transition-all tw-duration-300 group-hover:tw-bg-blue-500/20"></div>
          <div class="tw-flex tw-justify-between tw-items-start tw-relative">
            <div>
              <div class="tw-inline-flex tw-items-center tw-gap-2 tw-bg-blue-500/10 tw-text-blue-400 tw-px-3 tw-py-1.5 tw-rounded-full tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-mb-4 tw-border tw-border-blue-500/10">
                <i class="fas fa-calendar-check"></i> This Month
              </div>
              <h3 class="tw-text-white tw-text-4xl tw-font-black tw-mb-1 tw-tracking-tight">₹{{ formatAmount(summary.month_profit) }}</h3>
              <p class="tw-text-slate-400 tw-text-xs tw-font-medium">Net profit generated this month</p>
            </div>
            <div class="tw-w-14 tw-h-14 tw-rounded-2xl tw-bg-blue-500/10 tw-text-blue-400 tw-flex tw-items-center tw-justify-center tw-text-2xl tw-shadow-inner tw-border tw-border-blue-500/20">
              <i class="fas fa-chart-pie"></i>
            </div>
          </div>
        </div>

        <!-- Total Profit -->
        <div class="tw-bg-gradient-to-br tw-from-purple-900/50 tw-to-slate-900 tw-rounded-[32px] tw-p-8 tw-border tw-border-purple-500/20 tw-shadow-[0_0_40px_-10px_rgba(168,85,247,0.15)] tw-relative tw-overflow-hidden tw-group tw-transition-all tw-duration-300 hover:-tw-translate-y-1 hover:tw-shadow-[0_0_40px_-5px_rgba(168,85,247,0.25)] hover:tw-border-purple-500/40">
          <div class="tw-absolute -tw-right-10 -tw-top-10 tw-w-40 tw-h-40 tw-bg-purple-500/10 tw-rounded-full tw-blur-3xl tw-transition-all tw-duration-300 group-hover:tw-bg-purple-500/20"></div>
          <div class="tw-flex tw-justify-between tw-items-start tw-relative">
            <div>
              <div class="tw-inline-flex tw-items-center tw-gap-2 tw-bg-purple-500/10 tw-text-purple-400 tw-px-3 tw-py-1.5 tw-rounded-full tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-widest tw-mb-4 tw-border tw-border-purple-500/10">
                <i class="fas fa-wallet"></i> All Time
              </div>
              <h3 class="tw-text-white tw-text-4xl tw-font-black tw-mb-1 tw-tracking-tight">₹{{ formatAmount(summary.total_profit) }}</h3>
              <p class="tw-text-slate-400 tw-text-xs tw-font-medium">Total net profit accumulated</p>
            </div>
            <div class="tw-w-14 tw-h-14 tw-rounded-2xl tw-bg-purple-500/10 tw-text-purple-400 tw-flex tw-items-center tw-justify-center tw-text-2xl tw-shadow-inner tw-border tw-border-purple-500/20">
              <i class="fas fa-crown"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Controls -->
      <div class="tw-flex tw-flex-wrap tw-gap-4 tw-justify-between tw-items-end tw-mb-6">
        <div>
          <h5 class="tw-text-white tw-font-extrabold tw-text-2xl tw-m-0 tw-tracking-tight">Daily Summary Ledger</h5>
          <p class="tw-text-slate-400 tw-text-sm tw-mt-1 tw-mb-0">Track your daily recharges, withdrawals, and expenses in one place.</p>
        </div>
        <div class="tw-flex tw-items-center tw-gap-3">
            <button 
              v-if="hiddenDates.length > 0"
              class="tw-px-4 tw-h-11 tw-rounded-xl tw-bg-slate-800 hover:tw-bg-slate-700 tw-text-slate-300 tw-border-0 tw-flex tw-items-center tw-gap-2 tw-transition-all tw-text-xs tw-font-bold tw-cursor-pointer"
              @click="showHiddenModal = true"
            >
              <i class="fas fa-eye-slash"></i> Hidden ({{ hiddenDates.length }})
            </button>
            <button class="tw-w-11 tw-h-11 tw-rounded-xl tw-bg-indigo-600 hover:tw-bg-indigo-500 tw-text-white tw-border-0 tw-flex tw-items-center tw-justify-center tw-transition-all tw-duration-300 tw-shadow-[0_4px_14px_0_rgba(79,70,229,0.39)] hover:tw-shadow-[0_6px_20px_rgba(79,70,229,0.23)] hover:-tw-translate-y-0.5 tw-cursor-pointer" @click="fetchData(1)" title="Refresh">
              <i class="fas fa-sync-alt"></i>
            </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="tw-text-center tw-py-20">
        <div class="tw-w-16 tw-h-16 tw-border-4 tw-border-indigo-500/30 tw-border-t-indigo-500 tw-rounded-full tw-animate-spin tw-mx-auto tw-mb-4"></div>
        <div class="tw-text-slate-400 tw-font-medium">Syncing ledger records...</div>
      </div>

      <!-- Empty State -->
      <div v-else-if="records.length === 0" class="tw-bg-slate-900/30 tw-backdrop-blur-xl tw-rounded-[32px] tw-border tw-border-white/5 tw-text-center tw-py-24 tw-shadow-inner">
        <div class="tw-w-24 tw-h-24 tw-bg-slate-800/50 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-mx-auto tw-mb-6 tw-border tw-border-white/5">
          <i class="fas fa-folder-open tw-text-4xl tw-text-slate-500"></i>
        </div>
        <h4 class="tw-text-white tw-font-bold tw-text-xl tw-mb-2">No Records Found</h4>
        <p class="tw-text-slate-400 tw-max-w-sm tw-mx-auto">There is no account summary available for the selected dates. Try changing extreme date filters.</p>
      </div>

      <!-- Daily Records Flex Grid -->
      <div v-else class="tw-grid tw-grid-cols-1 md:tw-grid-cols-2 lg:tw-grid-cols-3 tw-gap-6 tw-auto-rows-fr">
        <div v-for="(record, index) in records" :key="index" class="tw-bg-slate-900/60 tw-backdrop-blur-xl tw-rounded-[24px] tw-border tw-border-white/10 tw-shadow-xl tw-flex tw-flex-col tw-overflow-hidden tw-transition-all tw-duration-300 hover:tw-border-white/20 hover:tw-shadow-2xl hover:-tw-translate-y-1">
          <!-- Card Header & Date -->
          <div class="tw-px-6 tw-py-5 tw-border-b tw-border-white/5 tw-bg-black/20 tw-flex tw-justify-between tw-items-center">
            <div class="tw-flex tw-items-center tw-gap-3">
              <div class="tw-w-10 tw-h-10 tw-rounded-xl tw-bg-indigo-500/10 tw-text-indigo-400 tw-flex tw-items-center tw-justify-center tw-text-lg tw-border tw-border-indigo-500/20 tw-shadow-inner">
                  <i class="fas fa-calendar-day"></i>
              </div>
              <div>
                  <h5 class="tw-text-white tw-font-extrabold tw-text-lg tw-m-0 tw-tracking-tight">{{ record.human_date }}</h5>
                  <span class="tw-text-[10px] tw-text-slate-400 tw-uppercase tw-tracking-widest tw-font-bold">Daily Report</span>
              </div>
            </div>
            <div class="tw-flex tw-items-center tw-gap-2">
              <div class="tw-px-2 tw-py-1 tw-rounded-md tw-text-[10px] tw-font-bold tw-uppercase tw-tracking-wider"
                  :class="record.net_profit >= 0 ? 'tw-bg-emerald-500/10 tw-text-emerald-400' : 'tw-bg-rose-500/10 tw-text-rose-400'">
                {{ record.net_profit >= 0 ? 'PROFIT' : 'LOSS' }}
              </div>
              <button class="tw-w-8 tw-h-8 tw-rounded-lg tw-bg-rose-500/10 tw-text-rose-400 hover:tw-bg-rose-500 tw-hover:tw-text-white tw-border tw-border-rose-500/20 tw-flex tw-items-center tw-justify-center tw-transition-all tw-cursor-pointer" title="Hide this day" @click="hideDay(record.date)">
                <i class="fas fa-trash-alt tw-text-xs"></i>
              </button>
            </div>
          </div>
          
          <!-- Card Body -->
          <div class="tw-px-6 tw-py-6 tw-flex-1 tw-flex tw-flex-col tw-gap-4">
              <!-- Recharge -->
              <div class="tw-flex tw-justify-between tw-items-center tw-bg-white/[0.02] tw-p-3 tw-rounded-xl tw-border tw-border-white/5">
                <div class="tw-text-slate-300 tw-font-semibold tw-text-sm tw-flex tw-items-center tw-gap-3">
                   <div class="tw-w-6 tw-h-6 tw-rounded-full tw-bg-emerald-500/20 tw-flex tw-items-center tw-justify-center tw-shadow-inner"><i class="fas fa-arrow-down tw-text-emerald-500 tw-text-[10px]"></i></div> Recharge
                </div>
                <div class="tw-font-black tw-text-white tw-text-base">
                  ₹{{ formatAmount(record.recharge) }}
                </div>
              </div>

              <!-- Withdraw -->
              <div class="tw-flex tw-justify-between tw-items-center tw-bg-white/[0.02] tw-p-3 tw-rounded-xl tw-border tw-border-white/5">
                <div class="tw-text-slate-300 tw-font-semibold tw-text-sm tw-flex tw-items-center tw-gap-3">
                   <div class="tw-w-6 tw-h-6 tw-rounded-full tw-bg-rose-500/20 tw-flex tw-items-center tw-justify-center tw-shadow-inner"><i class="fas fa-arrow-up tw-text-rose-500 tw-text-[10px]"></i></div> Withdraw
                </div>
                <div class="tw-font-black tw-text-white tw-text-base">
                  ₹{{ formatAmount(record.withdraw) }}
                </div>
              </div>

              <!-- Expenses -->
              <div class="tw-flex tw-justify-between tw-items-center tw-bg-white/[0.02] tw-p-3 tw-rounded-xl tw-border tw-border-white/5">
                <div class="tw-text-slate-300 tw-font-semibold tw-text-sm tw-flex tw-items-center tw-gap-3">
                   <div class="tw-w-6 tw-h-6 tw-rounded-full tw-bg-amber-500/20 tw-flex tw-items-center tw-justify-center tw-shadow-inner"><i class="fas fa-minus tw-text-amber-500 tw-text-[10px]"></i></div> Expenses
                </div>
                <div class="tw-font-black tw-text-white tw-text-base">
                  ₹{{ formatAmount(record.expenses_sum) }}
                </div>
              </div>

              <!-- Net Profit Premium Badged Row -->
              <div class="tw-mt-auto tw-pt-2 tw-flex tw-justify-between tw-items-end">
                <div class="tw-text-slate-400 tw-font-bold tw-text-xs tw-uppercase tw-tracking-widest">
                   Net Balance
                </div>
                <div class="tw-font-black tw-text-2xl tw-tracking-tight"
                     :class="record.net_profit >= 0 ? 'tw-text-emerald-400' : 'tw-text-rose-400'">
                  ₹{{ formatAmount(record.net_profit) }}
                </div>
              </div>
          </div>

          <!-- Card Footer (Action Buttons) -->
          <div class="tw-border-t tw-border-white/5 tw-bg-black/20 tw-flex tw-items-stretch">
            <button 
              class="tw-flex-1 tw-py-4 tw-px-2 tw-text-slate-400 hover:tw-text-emerald-400 hover:tw-bg-emerald-500/10 tw-bg-transparent tw-border-0 tw-border-r tw-border-white/5 tw-transition-colors tw-text-[11px] tw-font-extrabold tw-uppercase tw-tracking-widest tw-flex tw-items-center tw-justify-center tw-cursor-pointer" 
              @click="openAddExpense(record.date)"
            >
              <i class="fas fa-plus tw-mr-2"></i> Add Expense
            </button>
            <button 
              class="tw-flex-1 tw-py-4 tw-px-2 tw-text-slate-400 hover:tw-text-indigo-400 hover:tw-bg-indigo-500/10 tw-bg-transparent tw-border-0 tw-transition-colors tw-text-[11px] tw-font-extrabold tw-uppercase tw-tracking-widest tw-flex tw-items-center tw-justify-center tw-cursor-pointer" 
              @click="viewExpenses(record)"
            >
              <i class="fas fa-list-ul tw-mr-2"></i> View Exp. ({{ record.expenses_list?.length || 0 }})
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="ma-pagination-container mt-4">
        <ul class="pagination mb-0">
          <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
            <a class="page-link" href="#" @click.prevent="fetchData(pagination.current_page - 1)">&laquo;</a>
          </li>
          <li v-for="page in pagination.last_page" :key="page" class="page-item" :class="{ active: pagination.current_page === page }">
            <a class="page-link" href="#" @click.prevent="fetchData(page)">{{ page }}</a>
          </li>
          <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
            <a class="page-link" href="#" @click.prevent="fetchData(pagination.current_page + 1)">&raquo;</a>
          </li>
        </ul>
      </div>

      <!-- Add Expense Modal -->
      <div v-if="showAddExpenseModal" class="tw-fixed tw-inset-0 tw-z-[100] tw-flex tw-items-center tw-justify-center tw-px-4">
        <div class="tw-absolute tw-inset-0 tw-bg-black/60 tw-backdrop-blur-sm" @click="showAddExpenseModal = false"></div>
        <div class="tw-bg-slate-900 tw-border tw-border-slate-800 tw-rounded-2xl tw-shadow-2xl tw-w-full tw-max-w-md tw-relative tw-z-10 tw-overflow-hidden">
          <div class="tw-bg-slate-800 tw-p-4 tw-flex tw-justify-between tw-items-center">
            <h5 class="tw-text-white tw-font-bold tw-text-lg tw-m-0">Add Expense for {{ selectedDate }}</h5>
            <button @click="showAddExpenseModal = false" class="tw-text-slate-400 hover:tw-text-white tw-bg-transparent tw-border-0">
              <i class="fas fa-times tw-text-xl"></i>
            </button>
          </div>
          <form @submit.prevent="submitExpense">
            <div class="tw-p-5">
              <div class="tw-mb-4">
                <label class="form--label tw-text-xs">Expense Title (e.g., Server Cost) *</label>
                <input type="text" v-model="expenseForm.title" class="form--control" required>
              </div>
              <div class="tw-mb-4">
                <label class="form--label tw-text-xs">Amount (₹) *</label>
                <input type="number" step="0.01" min="0" v-model="expenseForm.amount" class="form--control" required>
              </div>
              <div class="tw-mb-4">
                <label class="form--label tw-text-xs">Details (Optional)</label>
                <textarea v-model="expenseForm.details" class="form--control" rows="3"></textarea>
              </div>
            </div>
            <div class="tw-p-4 tw-bg-slate-800/50 tw-text-right">
              <button type="button" class="ma-btn tw-mr-2" @click="showAddExpenseModal = false">Cancel</button>
              <button type="submit" class="ma-btn ma-btn--primary" :disabled="submitting">
                <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                Save Expense
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- View Expenses Modal -->
      <div v-if="showViewExpensesModal" class="tw-fixed tw-inset-0 tw-z-[100] tw-flex tw-items-center tw-justify-center tw-px-4">
        <div class="tw-absolute tw-inset-0 tw-bg-black/60 tw-backdrop-blur-sm" @click="showViewExpensesModal = false"></div>
        <div class="tw-bg-slate-900 tw-border tw-border-slate-800 tw-rounded-2xl tw-shadow-2xl tw-w-full tw-max-w-2xl tw-relative tw-z-10 tw-overflow-hidden">
          <div class="tw-bg-slate-800 tw-p-4 tw-flex tw-justify-between tw-items-center">
            <h5 class="tw-text-white tw-font-bold tw-text-lg tw-m-0">Expenses on {{ viewedRecord?.human_date }}</h5>
            <button @click="showViewExpensesModal = false" class="tw-text-slate-400 hover:tw-text-white tw-bg-transparent tw-border-0">
              <i class="fas fa-times tw-text-xl"></i>
            </button>
          </div>
          <div class="tw-p-5 tw-max-h-[60vh] tw-overflow-y-auto custom-scrollbar">
            <div v-if="viewedRecord?.expenses_list.length === 0" class="tw-text-center tw-text-slate-500 tw-py-4">
              No expenses recorded for this date.
            </div>
            <div v-else class="tw-space-y-3">
              <div v-for="exp in viewedRecord.expenses_list" :key="exp.id" class="tw-bg-slate-800 tw-rounded-xl tw-p-4 tw-flex tw-justify-between tw-items-center tw-border tw-border-white/5">
                 <div>
                    <h6 class="tw-text-white tw-font-bold tw-mb-1">{{ exp.title }}</h6>
                    <div class="tw-text-xs tw-text-slate-400" v-if="exp.details">{{ exp.details }}</div>
                 </div>
                 <div class="tw-text-right">
                    <div class="tw-text-amber-400 tw-font-black tw-mb-1">₹{{ formatAmount(exp.amount) }}</div>
                    <button class="ma-btn ma-btn--sm ma-btn--danger tw-px-2 tw-py-1 tw-text-[10px]" @click="deleteExpense(exp.id)">
                      <i class="fas fa-trash"></i>
                    </button>
                 </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Hidden Dates Modal -->
      <div v-if="showHiddenModal" class="tw-fixed tw-inset-0 tw-z-[100] tw-flex tw-items-center tw-justify-center tw-px-4">
        <div class="tw-absolute tw-inset-0 tw-bg-black/60 tw-backdrop-blur-sm" @click="showHiddenModal = false"></div>
        <div class="tw-bg-slate-900 tw-border tw-border-slate-800 tw-rounded-2xl tw-shadow-2xl tw-w-full tw-max-w-md tw-relative tw-z-10 tw-overflow-hidden">
          <div class="tw-bg-slate-800 tw-p-4 tw-flex tw-justify-between tw-items-center">
            <h5 class="tw-text-white tw-font-bold tw-text-lg tw-m-0">Hidden Record Dates</h5>
            <button @click="showHiddenModal = false" class="tw-text-slate-400 hover:tw-text-white tw-bg-transparent tw-border-0">
              <i class="fas fa-times tw-text-xl"></i>
            </button>
          </div>
          <div class="tw-p-5 tw-max-h-[50vh] tw-overflow-y-auto custom-scrollbar">
            <div v-if="hiddenDates.length === 0" class="tw-text-center tw-text-slate-500 tw-py-4">No hidden dates.</div>
            <div v-for="date in hiddenDates" :key="date" class="tw-flex tw-justify-between tw-items-center tw-bg-slate-800/50 tw-p-3 tw-rounded-xl tw-mb-2">
              <span class="tw-text-white tw-font-medium">{{ date }}</span>
              <button class="ma-btn ma-btn--sm ma-btn--primary tw-px-3 tw-py-1 tw-text-[10px]" @click="restoreDay(date)">
                <i class="fas fa-eye tw-mr-1"></i> Restore
              </button>
            </div>
          </div>
        </div>
      </div>

    </div>
  </MasterAdminLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import MasterAdminLayout from '../../components/master_admin/MasterAdminLayout.vue'
import api from '../../services/api'

export default {
  name: 'AccountLedger',
  components: {
    MasterAdminLayout
  },
  setup() {
    const loading = ref(true)
    const records = ref([])
    const summary = ref({
      today_profit: 0,
      month_profit: 0,
      total_profit: 0,
    })
    const pagination = ref({ current_page: 1, last_page: 1 })
    const filterDate = ref('')

    const showAddExpenseModal = ref(false)
    const showViewExpensesModal = ref(false)
    const submitting = ref(false)
    const selectedDate = ref('')
    const viewedRecord = ref(null)
    const hiddenDates = ref([])
    const showHiddenModal = ref(false)

    const expenseForm = ref({
      title: '',
      amount: '',
      details: ''
    })

    const formatAmount = (amount) => {
      if (!amount && amount !== 0) return '0.00'
      return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    }

    const fetchData = async (page = 1) => {
      loading.value = true
      try {
        let url = `/admin/account-ledger?page=${page}`
        if (filterDate.value) {
            url += `&date=${filterDate.value}`
        }
        const response = await api.get(url)
        if (response.data.status === 'success') {
          records.value = response.data.data.daily_records
          summary.value = response.data.data.summary
          pagination.value = response.data.data.pagination
          hiddenDates.value = response.data.data.hidden_dates || []
        }
      } catch (error) {
        console.error('Error fetching ledger data:', error)
        if (window.notify) window.notify('error', 'Failed to load ledger data')
      } finally {
        loading.value = false
      }
    }

    const openAddExpense = (date) => {
      selectedDate.value = date
      expenseForm.value = { title: '', amount: '', details: '' }
      showAddExpenseModal.value = true
    }

    const viewExpenses = (record) => {
      viewedRecord.value = record
      showViewExpensesModal.value = true
    }

    const submitExpense = async () => {
      if (!expenseForm.value.title || !expenseForm.value.amount) return
      submitting.value = true
      try {
        const payload = {
          date: selectedDate.value,
          ...expenseForm.value
        }
        const response = await api.post('/admin/account-ledger/add-expense', payload)
        if (response.data.status === 'success') {
          if (window.notify) window.notify('success', 'Expense added successfully')
          showAddExpenseModal.value = false
          fetchData(pagination.value.current_page)
        }
      } catch (error) {
        if (window.notify) {
          const m = error.response?.data?.message
          const msg = (m?.error?.[0] || m?.success?.[0] || (Array.isArray(m) ? m[0] : m) || 'Failed to add expense')
          window.notify('error', msg)
        }
      } finally {
        submitting.value = false
      }
    }

    const deleteExpense = async (id) => {
      if (!window.confirm('Are you sure you want to delete this expense?')) return
      try {
        const response = await api.delete(`/admin/account-ledger/expense/${id}`)
        if (response.data.status === 'success') {
          if (window.notify) window.notify('success', 'Expense deleted')
          // Close modal and refresh to update totals
          showViewExpensesModal.value = false
          fetchData(pagination.value.current_page)
        }
      } catch (error) {
        if (window.notify) window.notify('error', 'Failed to delete expense')
      }
    }

    const hideDay = async (date) => {
      if (!window.confirm(`Are you sure you want to hide data for ${date} from your ledger view? This won't delete user data, just hide this day's summary for you.`)) return
      try {
        const res = await api.post('/admin/account-ledger/hide-date', { date })
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Day hidden successfully')
          fetchData(pagination.value.current_page)
        }
      } catch (e) {
        if (window.notify) window.notify('error', 'Failed to hide day')
      }
    }

    const restoreDay = async (date) => {
      try {
        const res = await api.post('/admin/account-ledger/restore-date', { date })
        if (res.data?.status === 'success') {
          if (window.notify) window.notify('success', 'Day restored successfully')
          fetchData(pagination.value.current_page)
          if (hiddenDates.value.length === 1) showHiddenModal.value = false
        }
      } catch (e) {
        if (window.notify) window.notify('error', 'Failed to restore day')
      }
    }

    onMounted(() => {
      fetchData()
    })

    return {
      loading,
      records,
      summary,
      pagination,
      filterDate,
      formatAmount,
      fetchData,
      showAddExpenseModal,
      showViewExpensesModal,
      openAddExpense,
      viewExpenses,
      submitExpense,
      submitting,
      selectedDate,
      expenseForm,
      viewedRecord,
      deleteExpense,
      hideDay,
      hiddenDates,
      showHiddenModal,
      restoreDay
    }
  }
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}
</style>
