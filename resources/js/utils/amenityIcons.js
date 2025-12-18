import {
  Wifi,
  Wind,
  Droplets,
  ChefHat,
  UtensilsCrossed,
  ShowerHead,
  Bath,
  Bed,
  Package,
  Square,
  WashingMachine,
  Shirt,
  Refrigerator,
  Home,
} from 'lucide-vue-next'

/**
 * Map các tiện ích (key tiếng Anh) với icon tương ứng
 */
export const amenityIconMap = {
  Wifi: Wifi,
  AirConditioning: Wind,
  HotWater: Droplets,
  PrivateKitchen: ChefHat,
  SharedKitchen: UtensilsCrossed,
  SharedBathroom: ShowerHead,
  PrivateBathroom: Bath,
  Bed: Bed,
  Wardrobe: Package,
  Refrigerator: Refrigerator,
  Balcony: Square,
  SharedWashingMachine: WashingMachine,
  PrivateWashingMachine: WashingMachine,
  SharedDryer: Shirt,
  PrivateDryer: Shirt,
}

/**
 * Map key tiếng Anh sang tên hiển thị tiếng Việt
 */
export const amenityNameMap = {
  Wifi: 'Wifi',
  AirConditioning: 'Điều hòa',
  HotWater: 'Nóng lạnh',
  PrivateKitchen: 'Bếp riêng',
  SharedKitchen: 'Bếp chung',
  SharedBathroom: 'Vệ sinh chung',
  PrivateBathroom: 'Vệ sinh khép kín',
  Bed: 'Giường',
  Wardrobe: 'Tủ',
  Refrigerator: 'Tủ lạnh',
  Balcony: 'Ban công',
  SharedWashingMachine: 'Máy giặt chung',
  PrivateWashingMachine: 'Máy giặt riêng',
  SharedDryer: 'Máy sấy quần áo chung',
  PrivateDryer: 'Máy sấy quần áo riêng',
}

/**
 * Lấy icon component cho một tiện ích
 * @param {string} amenityKey - Key tiếng Anh của tiện ích
 * @returns {Component|Home} - Icon component hoặc Home icon mặc định
 */
export function getAmenityIcon(amenityKey) {
  return amenityIconMap[amenityKey] || Home
}

/**
 * Lấy tên hiển thị tiếng Việt cho một tiện ích
 * @param {string} amenityKey - Key tiếng Anh của tiện ích
 * @returns {string} - Tên tiếng Việt hoặc key gốc nếu không tìm thấy
 */
export function getAmenityName(amenityKey) {
  return amenityNameMap[amenityKey] || amenityKey
}
