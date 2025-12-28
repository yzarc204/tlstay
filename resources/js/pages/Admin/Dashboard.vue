<template>
  <Head title="Dashboard" />
  <AdminLayout title="Dashboard">
    <div class="space-y-6">
      <!-- Welcome Section -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">
          Chào mừng đến với Admin Panel
        </h2>
        <p class="text-gray-600">
          Quản lý hệ thống nhà/phòng trọ Thăng Long Stay
        </p>
      </div>

      <!-- Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <div>
                            <p class="text-sm font-medium text-gray-600">
                                Tổng Nhà trọ
                            </p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">
                                {{ stats.total_houses }}
                            </p>
            </div>
                        <div
                            class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center"
                        >
              <BuildingOffice2Icon class="h-6 w-6 text-primary" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <div>
                            <p class="text-sm font-medium text-gray-600">
                                Tổng Phòng
                            </p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">
                                {{ stats.total_rooms }}
                            </p>
              <p class="text-xs text-gray-500 mt-1">
                                Đang thuê: {{ stats.occupied_rooms }} | Trống:
                                {{ stats.available_rooms }}
              </p>
            </div>
                        <div
                            class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center"
                        >
              <Squares2X2Icon class="h-6 w-6 text-blue-600" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <div>
                            <p class="text-sm font-medium text-gray-600">
                                Đặt phòng
                            </p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">
                                {{ stats.total_bookings }}
                            </p>
              <p class="text-xs text-gray-500 mt-1">
                Tháng này: {{ stats.bookings_this_month }}
              </p>
            </div>
                        <div
                            class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center"
                        >
              <CalendarIcon class="h-6 w-6 text-green-600" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <div>
                            <p class="text-sm font-medium text-gray-600">
                                Khách hàng
                            </p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">
                                {{ stats.total_customers }}
                            </p>
              <p class="text-xs text-gray-500 mt-1">
                Tổng tài khoản: {{ stats.total_users }}
              </p>
            </div>
                        <div
                            class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center"
                        >
              <UsersIcon class="h-6 w-6 text-purple-600" />
            </div>
          </div>
        </div>
      </div>

      <!-- Additional Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <div>
                            <p class="text-sm font-medium text-gray-600">
                                Hóa đơn
                            </p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">
                                {{ stats.total_invoices }}
                            </p>
              <p class="text-xs text-gray-500 mt-1">
                                Chưa thanh toán: {{ stats.pending_invoices }} |
                                Đã thanh toán: {{ stats.paid_invoices }}
              </p>
            </div>
                        <div
                            class="h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center"
                        >
              <DocumentTextIcon class="h-6 w-6 text-yellow-600" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <div>
                            <p class="text-sm font-medium text-gray-600">
                                Doanh thu tháng này
                            </p>
              <p class="text-2xl font-bold text-gray-900 mt-1">
                {{ formatCurrency(stats.revenue_this_month) }}
              </p>
            </div>
                        <div
                            class="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center"
                        >
              <BanknotesIcon class="h-6 w-6 text-emerald-600" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center justify-between">
            <div>
                            <p class="text-sm font-medium text-gray-600">
                                Đặt phòng chờ xác nhận
                            </p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">
                                {{ stats.pending_bookings }}
                            </p>
              <p class="text-xs text-gray-500 mt-1">
                Đã xác nhận: {{ stats.confirmed_bookings }}
              </p>
            </div>
                        <div
                            class="h-12 w-12 rounded-full bg-orange-100 flex items-center justify-center"
                        >
              <ClockIcon class="h-6 w-6 text-orange-600" />
            </div>
          </div>
        </div>
      </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Revenue Chart -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Doanh thu 6 tháng gần nhất
                    </h3>
                    <div class="h-64">
                        <LineChart
                            :data="revenueChartData"
                            :options="revenueChartOptions"
                        />
                    </div>
                </div>

                <!-- Bookings Chart -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Đặt phòng 6 tháng gần nhất
                    </h3>
                    <div class="h-64">
                        <BarChart
                            :data="bookingsChartData"
                            :options="bookingsChartOptions"
                        />
                    </div>
                </div>
            </div>

            <!-- Room Status Chart -->
      <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    Trạng thái phòng
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="h-64 flex items-center justify-center">
                        <PieChart
                            :data="roomStatusChartData"
                            :options="roomStatusChartOptions"
                        />
                    </div>
                    <div class="flex flex-col justify-center space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-4 h-4 rounded-full bg-green-500"></div>
                            <div>
                                <p class="font-semibold text-gray-900">
                                    Phòng trống
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ chartData?.roomStatus?.available || 0 }} phòng
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-4 h-4 rounded-full bg-blue-500"></div>
                            <div>
                                <p class="font-semibold text-gray-900">
                                    Phòng đang thuê
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ chartData?.roomStatus?.occupied || 0 }} phòng
                                </p>
                            </div>
                        </div>
                    </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";
import AdminLayout from "@/layouts/AdminLayout.vue";
import {
  BuildingOffice2Icon,
  Squares2X2Icon,
  CalendarIcon,
  UsersIcon,
  DocumentTextIcon,
  BanknotesIcon,
    ClockIcon,
} from "@heroicons/vue/24/outline";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
} from "chart.js";
import { Line, Bar, Pie } from "vue-chartjs";

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend
);

// Đăng ký các component biểu đồ
const LineChart = Line;
const BarChart = Bar;
const PieChart = Pie;

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({
      total_houses: 0,
      total_rooms: 0,
      occupied_rooms: 0,
      available_rooms: 0,
      total_bookings: 0,
      pending_bookings: 0,
      confirmed_bookings: 0,
      total_customers: 0,
      total_users: 0,
      total_invoices: 0,
      pending_invoices: 0,
      paid_invoices: 0,
      bookings_this_month: 0,
      revenue_this_month: 0,
    }),
  },
    chartData: {
        type: Object,
        default: () => ({
            revenue: { labels: [], data: [] },
            bookings: { labels: [], data: [] },
            roomStatus: { available: 0, occupied: 0 },
        }),
    },
});

const formatCurrency = (amount) => {
    if (!amount) return "0 ₫";
    return new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
    }).format(amount);
};

// Revenue Chart Data
const revenueChartData = computed(() => ({
    labels: props.chartData?.revenue?.labels || [],
    datasets: [
        {
            label: "Doanh thu (VNĐ)",
            data: props.chartData?.revenue?.data || [],
            borderColor: "rgb(34, 197, 94)",
            backgroundColor: "rgba(34, 197, 94, 0.1)",
            tension: 0.4,
            fill: true,
        },
    ],
}));

const revenueChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: "top",
        },
        tooltip: {
            callbacks: {
                label: function (context) {
                    return formatCurrency(context.parsed.y);
                },
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: function (value) {
                    if (value >= 1000000) {
                        return (value / 1000000).toFixed(1) + "M";
                    } else if (value >= 1000) {
                        return (value / 1000).toFixed(0) + "K";
                    }
                    return value;
                },
            },
        },
    },
};

// Bookings Chart Data
const bookingsChartData = computed(() => ({
    labels: props.chartData?.bookings?.labels || [],
    datasets: [
        {
            label: "Số đặt phòng",
            data: props.chartData?.bookings?.data || [],
            backgroundColor: "rgba(59, 130, 246, 0.5)",
            borderColor: "rgb(59, 130, 246)",
            borderWidth: 1,
        },
    ],
}));

const bookingsChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: "top",
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1,
            },
        },
    },
};

// Room Status Chart Data
const roomStatusChartData = computed(() => ({
    labels: ["Phòng trống", "Phòng đang thuê"],
    datasets: [
        {
            data: [
                props.chartData?.roomStatus?.available || 0,
                props.chartData?.roomStatus?.occupied || 0,
            ],
            backgroundColor: ["rgb(34, 197, 94)", "rgb(59, 130, 246)"],
            borderColor: ["rgb(22, 163, 74)", "rgb(37, 99, 235)"],
            borderWidth: 2,
        },
    ],
}));

const roomStatusChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            callbacks: {
                label: function (context) {
                    return context.label + ": " + context.parsed + " phòng";
                },
            },
        },
    },
};
</script>
