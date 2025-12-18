# Website cho thuê phòng trọ ngắn và dài hạn THANG LONG STAY

## Ý tưởng
- Website THANG LONG STAY là website dịch vụ thuê phòng trọ theo chu kì ngày/tuần/tháng. Khách hàng có thể dễ dàng tìm kiếm, tham khảo đánh giá, thuê và thanh toán các chi phí lưu trú ngay trên website. Website hướng tới đối tượng là sinh viên cần tìm nhà trọ. Giao diện website thân thiện với người dùng, phù hợp với đối tượng Gen Z.

## Công nghệ sử dụng
- Backend: Laravel
- Frontend: Inertia + Vue 3 + Tailwind + Pinia
- Database: MySQL
- Payment gate: VNPAY sandbox (for testing)

## Module Quản lí nhà trọ, phòng trọ
- Người quản lí có thể thêm/sửa danh sách nhà trọ, phòng trọ của mình lên trên website
- Mỗi nhà trọ gồm có các thông tin sau: tên nhà trọ, địa chỉ, tọa độ, hình ảnh, mô tả nhà trọ, các dịch vụ tiện nghi mà nhà trọ cung cấp (nóng lạnh, điều hòa, bếp...), khoảng cách tới trung tâm (trường đại học Thăng Long)
- Mỗi nhà trọ có nhiều tầng, mỗi tầng có nhiều phòng với sơ đồ tùy chỉnh. Mỗi phòng trọ có giá thuê khác nhau, Giá gốc là giá thuê theo ngày, khi khách thuê theo tuần/tháng sẽ được áp dụng ưu đãi giảm từ 5 - 10%
- Với khách thuê theo tháng, giá thuê nhà chưa bao gồm dịch vụ điện, nước. Người quản lí sẽ thêm giá dịch vụ này cho phòng trọ của mình.
- Người quản lí có quyền chỉnh sửa người đang thuê phòng và ngày bắt đầu/ngày kết thúc thuê của phòng đó 

## Module Địa chỉ: Phường, Đường
- Người quản lí có thể thêm/sửa/xóa phường
- Người quản lí có thể thêm/sửa/xóa đường
- Mỗi một phường có thể có nhiều đường (mối quan hệ ràng buộc)
- Khi thêm một nhà trọ, người quản lí có thể thêm các thông tin sau: Số nhà, ngõ ngách hẻm + đường + phường 

## Module Quản lí hợp đồng
- Cho phép người quản lí tải lên mẫu hợp đồng thuê, lưu trú dưới dạng file .blade.php

## Module Quản lí khuyến mãi
- Người quản lí có thể thêm các voucher khuyến mãi với dạng đơn giản: áp dụng cho đơn tối thiểu bao nhiêu tiền, giảm bao nhiêu %, giảm tối đa bao nhiêu %, mỗi tài khoản được dùng bao nhiêu lần, thời gian voucher hợp lệ
- Thống kê lượt sử dụng của từng voucher

## Module Quản lí banner, slider, popup promotion
- Người quản lí có quyền quản lí banner, slider, popup khuyến mãi cho website

## Module Quản lí tài khoản
- Người quản lí có quyền xem thông tin các tài khoản trong hệ thống
- Người quản lí có thể ban 1 tài khoản kèm theo lí do ban tài khoản đó

## Module Xác thực người dùng
- Hệ thống có 2 phân quyền người dùng: quản lí và khách hàng
- Người dùng phải đăng ký tài khoản với các thông tin: Email, mật khẩu, số điện thoại
- Người dùng phải bổ sung các thông tin sau nếu muốn sử dụng tính năng thuê phòng: địa chỉ thường trú, căn cước công dân, ngày sinh, giới tính
- Website có trang đăng nhập cho người quản lí và người dùng riêng biệt

## Module Tìm nhà/phòng trọ
- Khách hàng có thể tìm kiếm, lọc nhà trọ theo giá tiền, khoảng cách, các tiện nghi, đánh giá
- Khách hàng có thể sắp xếp nhà trọ dựa trên lượt thuê, giá tiền, đánh giá

## Module Thuê phòng
- Khách hàng có thể thuê phòng thuê luồng sau: chọn nhà trọ -> chọn phòng trọ -> chọn thời gian thuê -> thêm danh sách người thuê phòng (nếu có từ 2 người trở lên)
- Giả sử 1 khách hàng thuê phòng trong 50 ngày, thì giá thuê sẽ tính như sau: giá thuê 1 tháng + giá thuê 2 tuần + giá thuê 6 ngày

## Module Quản lí nhà trọ, hóa đơn của khách
- Cho phép khách hàng xem và chỉnh sửa danh sách người trong phòng trọ
- Cho phép khách hàng xem hóa đơn dịch vụ và thanh toán hóa đơn này
- Cho phép khách hàng lịch sử thuê phòng
- Đánh giá nhà trọ đã thuê từ 1 - 5 sao kèm bình luận và hình ảnh

