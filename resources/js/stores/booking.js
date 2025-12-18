import { defineStore } from 'pinia'
import axios from 'axios'

export const useBookingStore = defineStore('booking', {
  state: () => ({
    bookings: [],
    invoices: [],
    loading: false,
  }),

  getters: {
    activeBookings: (state) => {
      return state.bookings.filter((b) => b.status === 'active')
    },

    currentMonthInvoices: (state) => {
      const currentMonth = new Date().getMonth() + 1
      const currentYear = new Date().getFullYear()
      const monthStr = `${currentMonth}/${currentYear}`
      return state.invoices.filter((inv) => inv.month === monthStr)
    },
  },

  actions: {
    async createBooking(bookingData) {
      try {
        // TODO: Implement Laravel API call
        // const response = await axios.post('/api/bookings', {
        //   ...bookingData,
        //   created_at: new Date().toISOString().split('T')[0],
        //   status: 'active',
        // })
        // return { success: true, data: response.data }
        
        return { success: false, message: 'Chưa được implement - cần gọi Laravel API' }
      } catch (error) {
        console.error('Error creating booking:', error)
        return { success: false, message: 'Đã có lỗi xảy ra' }
      }
    },

    async fetchUserBookings(userId) {
      this.loading = true
      try {
        // TODO: Implement Laravel API call
        // const response = await axios.get(`/api/users/${userId}/bookings`)
        // this.bookings = response.data
        
        this.bookings = []
      } catch (error) {
        console.error('Error fetching bookings:', error)
        this.bookings = []
      } finally {
        this.loading = false
      }
    },

    async fetchUserInvoices(userId) {
      this.loading = true
      try {
        // TODO: Implement Laravel API call
        // const response = await axios.get(`/api/users/${userId}/invoices`)
        // this.invoices = response.data
        
        this.invoices = []
      } catch (error) {
        console.error('Error fetching invoices:', error)
        this.invoices = []
      } finally {
        this.loading = false
      }
    },
  },
})
