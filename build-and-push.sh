#!/bin/bash

# Script để build frontend, commit và push lên git
# Màu sắc cho output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 Bắt đầu build frontend và push lên git...${NC}"
echo ""

# Kiểm tra xem có đang ở trong git repository không
if [ ! -d ".git" ]; then
    echo -e "${RED}❌ Không phải git repository!${NC}"
    exit 1
fi

# Kiểm tra xem có thay đổi chưa commit không (trừ public/build)
UNCOMMITTED=$(git status --porcelain | grep -v "public/build" | grep -v "^?? public/build")
if [ ! -z "$UNCOMMITTED" ]; then
    echo -e "${YELLOW}⚠️  Có thay đổi chưa commit:${NC}"
    echo "$UNCOMMITTED"
    echo ""
    read -p "Bạn có muốn commit các thay đổi này trước khi build không? (y/n): " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${BLUE}📝 Đang commit các thay đổi...${NC}"
        git add -A
        read -p "Nhập commit message: " COMMIT_MSG
        git commit -m "$COMMIT_MSG"
        if [ $? -ne 0 ]; then
            echo -e "${RED}❌ Lỗi khi commit!${NC}"
            exit 1
        fi
        echo -e "${GREEN}✅ Đã commit thành công${NC}"
        echo ""
    fi
fi

# Kiểm tra xem có package.json không
if [ ! -f "package.json" ]; then
    echo -e "${RED}❌ Không tìm thấy package.json!${NC}"
    exit 1
fi

# Kiểm tra xem có node_modules không
if [ ! -d "node_modules" ]; then
    echo -e "${YELLOW}⚠️  node_modules không tồn tại, đang cài đặt dependencies...${NC}"
    npm install
    if [ $? -ne 0 ]; then
        echo -e "${RED}❌ Lỗi khi cài đặt dependencies!${NC}"
        exit 1
    fi
    echo -e "${GREEN}✅ Đã cài đặt dependencies${NC}"
    echo ""
fi

# Build frontend
echo -e "${BLUE}🔨 Đang build frontend...${NC}"
npm run build

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Lỗi khi build frontend!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Build frontend thành công${NC}"
echo ""

# Kiểm tra xem có file build không
if [ ! -d "public/build" ] || [ -z "$(ls -A public/build 2>/dev/null)" ]; then
    echo -e "${YELLOW}⚠️  Không tìm thấy file build trong public/build${NC}"
    echo -e "${YELLOW}⚠️  Có thể build đã được output ở nơi khác hoặc chưa build thành công${NC}"
    read -p "Bạn có muốn tiếp tục commit và push không? (y/n): " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Add các file build (force add vì có thể bị ignore)
echo -e "${BLUE}📦 Đang thêm các file build vào git...${NC}"
git add -f public/build/ 2>/dev/null || true

# Kiểm tra xem có thay đổi nào để commit không
if git diff --staged --quiet; then
    echo -e "${YELLOW}⚠️  Không có thay đổi nào để commit${NC}"
else
    # Commit các file build
    echo -e "${BLUE}📝 Đang commit các file build...${NC}"
    TIMESTAMP=$(date +"%Y-%m-%d %H:%M:%S")
    git commit -m "Build frontend assets - $TIMESTAMP"
    
    if [ $? -ne 0 ]; then
        echo -e "${RED}❌ Lỗi khi commit!${NC}"
        exit 1
    fi
    
    echo -e "${GREEN}✅ Đã commit thành công${NC}"
    echo ""
    
    # Push lên git
    echo -e "${BLUE}📤 Đang push lên git...${NC}"
    git push
    
    if [ $? -ne 0 ]; then
        echo -e "${RED}❌ Lỗi khi push!${NC}"
        exit 1
    fi
    
    echo -e "${GREEN}✅ Đã push thành công lên git${NC}"
fi

echo ""
echo -e "${GREEN}🎉 Hoàn thành!${NC}"
