import { defineStore } from 'pinia'
import axios from 'axios'

export const useHouseStore = defineStore('house', {
  state: () => ({
    houses: [],
    selectedHouse: null,
    rooms: [],
    loading: false,
    filters: {
      priceRange: [0, 10000000],
      location: '',
      amenities: [],
    },
  }),

  getters: {
    filteredHouses: (state) => {
      // Tính giá theo tháng từ giá ngày (giảm 20%)
      const calculateMonthlyPrice = (pricePerDay) => {
        if (!pricePerDay) return 0
        return Math.round(pricePerDay * 30 * 0.8)
      }

      return state.houses.filter((house) => {
        const monthlyPrice = calculateMonthlyPrice(house.pricePerDay)
        const priceMatch =
          monthlyPrice >= state.filters.priceRange[0] &&
          monthlyPrice <= state.filters.priceRange[1]
        const locationMatch =
          !state.filters.location ||
          house.address.toLowerCase().includes(state.filters.location.toLowerCase())
        return priceMatch && locationMatch
      })
    },

    roomsByFloor: (state) => {
      const floors = {}
      state.rooms.forEach((room) => {
        if (!floors[room.floor]) {
          floors[room.floor] = []
        }
        floors[room.floor].push(room)
      })
      return floors
    },
  },

  actions: {
    async fetchHouses() {
      this.loading = true
      try {
        // TODO: Implement Laravel API call
        // const response = await axios.get('/api/houses')
        // this.houses = response.data
        
        this.houses = []
      } catch (error) {
        console.error('Error fetching houses:', error)
        this.houses = []
      } finally {
        this.loading = false
      }
    },

    async fetchHouseById(id) {
      this.loading = true
      try {
        // TODO: Implement Laravel API call
        // const response = await axios.get(`/api/houses/${id}`)
        // this.selectedHouse = response.data
        // return this.selectedHouse
        
        this.selectedHouse = null
        return null
      } catch (error) {
        console.error('Error fetching house:', error)
        if (error.response?.status) {
          console.error('Response error:', error.response.status, error.response.data?.message)
        } else {
          console.error('Error:', error.message)
        }
        throw error // Re-throw để component có thể xử lý
      } finally {
        this.loading = false
      }
    },

    async fetchRoomsByHouseId(houseId) {
      this.loading = true
      try {
        // TODO: Implement Laravel API call
        // const response = await axios.get(`/api/houses/${houseId}/rooms`)
        // this.rooms = response.data.map(room => ({
        //   ...room,
        //   pricePerDay: room.price_per_day || room.price || 0,
        // }))
        // return this.rooms
        
        this.rooms = []
        return []
      } catch (error) {
        console.error('Error fetching rooms:', error)
        if (error.response?.status) {
          console.error('Response error:', error.response.status, error.response.data?.message)
        } else {
          console.error('Error:', error.message)
        }
        this.rooms = []
        return []
      } finally {
        this.loading = false
      }
    },

    setFilters(filters) {
      this.filters = { ...this.filters, ...filters }
    },
  },
})
