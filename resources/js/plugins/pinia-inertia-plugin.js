export function piniaInertiaPlugin({ store }) {
  // Chỉ áp dụng cho auth store
  if (store.$id === 'auth') {
    // Sync user từ Inertia page props khi store được tạo
    const syncUser = () => {
      try {
        if (typeof window !== 'undefined' && window.Inertia) {
          const page = window.Inertia.page
          if (page?.props?.auth?.user) {
            store.user = page.props.auth.user
          }
        }
      } catch (e) {
        // Ignore
      }
    }

    // Sync ngay khi store được tạo
    syncUser()

    // Listen cho Inertia events để sync khi page thay đổi
    if (typeof window !== 'undefined' && window.Inertia) {
      const originalVisit = window.Inertia.visit
      window.Inertia.visit = function(...args) {
        const result = originalVisit.apply(this, args)
        // Sync sau khi visit
        setTimeout(syncUser, 100)
        return result
      }
    }
  }
}
