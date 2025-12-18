#!/bin/bash

# Script để nén project Laravel thành file zip để deploy
# Loại trừ các file không cần thiết như .env, node_modules, vendor, file người dùng tự thêm

# Màu sắc cho output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Tên project (lấy từ thư mục hiện tại)
PROJECT_NAME=$(basename "$(pwd)")
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
ZIP_NAME="${PROJECT_NAME}_${TIMESTAMP}.zip"

echo -e "${GREEN}🚀 Bắt đầu đóng gói project Laravel...${NC}"
echo ""

# Kiểm tra xem có file zip cũ không và xóa
if [ -f "${PROJECT_NAME}_"*.zip ]; then
    echo -e "${YELLOW}⚠️  Đang xóa file zip cũ...${NC}"
    rm -f "${PROJECT_NAME}_"*.zip
fi

# Chạy optimize:clear để xóa cache
echo -e "${GREEN}🧹 Đang xóa cache Laravel...${NC}"
php artisan optimize:clear > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Đã xóa cache Laravel${NC}"
else
    echo -e "${YELLOW}⚠️  Không thể chạy php artisan optimize:clear (có thể do chưa cài đặt dependencies)${NC}"
fi
echo ""

# Chạy npm run build để build assets
echo -e "${GREEN}🔨 Đang build assets với npm...${NC}"
if [ -f "package.json" ]; then
    npm run build
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Đã build assets thành công${NC}"
    else
        echo -e "${RED}❌ Lỗi khi build assets!${NC}"
        echo -e "${YELLOW}⚠️  Tiếp tục đóng gói nhưng có thể thiếu file build...${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  Không tìm thấy package.json, bỏ qua bước build${NC}"
fi
echo ""

# Tạo file zip với các file cần thiết
echo -e "${GREEN}📦 Đang tạo file zip...${NC}"

zip -r "$ZIP_NAME" . \
    -x "*.env*" \
    -x "*.log" \
    -x "node_modules/*" \
    -x "vendor/*" \
    -x ".git/*" \
    -x ".cursor/*" \
    -x ".idea/*" \
    -x ".vscode/*" \
    -x ".DS_Store" \
    -x "Thumbs.db" \
    -x "*.zip" \
    -x "package.sh" \
    -x "project.md" \
    -x "Hợp đồng thuê trọ.docx" \
    -x "storage/logs/*.log" \
    -x "storage/framework/cache/data/*" \
    -x "storage/framework/sessions/*" \
    -x "storage/framework/views/*.php" \
    -x "storage/pail/*" \
    -x "public/hot" \
    -x "public/storage" \
    -x "public/storage/*" \
    -x "bootstrap/cache/*.php" \
    -x ".phpunit.cache/*" \
    -x ".phpunit.result.cache" \
    -x "Homestead.json" \
    -x "Homestead.yaml" \
    -x "*.phpunit.result.cache" \
    -x "auth.json" \
    -x ".phpactor.json" \
    -x ".fleet/*" \
    -x ".nova/*" \
    -x ".zed/*" \
    -x "storage/*.key" \
    > /dev/null 2>&1

# Kiểm tra kết quả
if [ $? -eq 0 ]; then
    FILE_SIZE=$(du -h "$ZIP_NAME" | cut -f1)
    echo ""
    echo -e "${GREEN}✅ Đóng gói thành công!${NC}"
    echo -e "${GREEN}📁 File: ${ZIP_NAME}${NC}"
    echo -e "${GREEN}📊 Kích thước: ${FILE_SIZE}${NC}"
    echo ""
    echo -e "${YELLOW}📝 Lưu ý khi deploy:${NC}"
    echo "  1. Giải nén file zip trên server"
    echo "  2. Chạy: composer install --no-dev --optimize-autoloader"
    echo "  3. Chạy: npm install && npm run build"
    echo "  4. Tạo file .env từ .env.example và cấu hình"
    echo "  5. Chạy: php artisan key:generate"
    echo "  6. Chạy: php artisan migrate"
    echo "  7. Tạo symbolic link: php artisan storage:link"
    echo "  8. Set quyền: chmod -R 775 storage bootstrap/cache"
    echo ""
else
    echo ""
    echo -e "${RED}❌ Có lỗi xảy ra khi đóng gói!${NC}"
    exit 1
fi
