import { defineStore } from 'pinia'

export const useSiteStore = defineStore('site', {
  state: () => ({
    // Thông tin website
    siteName: 'THANG LONG STAY',
    description:
      'Nền tảng cho thuê phòng trọ hàng đầu Việt Nam. Tìm kiếm và đặt phòng trọ dễ dàng, nhanh chóng.',

    // Thông tin liên hệ
    contact: {
      email: 'contact@tlstay.com',
      phone: '+84 123 456 789',
      address: 'Việt Nam',
    },

    // Social media links (nếu cần)
    social: {
      facebook: '#',
      twitter: '#',
      instagram: '#',
    },
  }),

  getters: {
    // Getter để lấy tên website
    getSiteName: (state) => state.siteName,

    // Getter để lấy email
    getEmail: (state) => state.contact.email,

    // Getter để lấy số điện thoại
    getPhone: (state) => state.contact.phone,

    // Getter để lấy địa chỉ
    getAddress: (state) => state.contact.address,

    // Getter để lấy mô tả
    getDescription: (state) => state.description,

    // Getter để lấy toàn bộ thông tin liên hệ
    getContactInfo: (state) => state.contact,
  },
})
