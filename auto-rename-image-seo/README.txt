=== Auto Rename Image SEO ===
Contributors: thuongtruong101
Donate link: https://github.com/thuongtruong101/auto-rename-image-seo
Tags: seo, images, media, filenames, uploads, rename
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Tự động đổi tên file ảnh khi upload thành tên thân thiện SEO, giúp cải thiện thứ hạng tìm kiếm.

== Description ==

Plugin **Auto Rename Image SEO** tự động đổi tên file ảnh khi upload lên WordPress thành tên thân thiện với công cụ tìm kiếm (SEO). 

Thay vì file ảnh có tên như `IMG_12345.jpg` hay `DSC_0001.png`, plugin sẽ tự động đổi tên thành tên dựa trên:

* **Tiêu đề bài viết** - Sử dụng tiêu đề của bài viết đang upload
* **Tên file gốc** - Giữ nguyên tên file nhưng loại bỏ các ký tự đặc biệt

Bạn có thể cấu hình:

* Bật/Tắt tính năng
* Chọn phương thức đổi tên (Tiêu đề bài viết, Tên file gốc, hoặc kết hợp)
* Chọn ký tự phân cách (mặc định: `-`)
* Chiều dài tối đa của tên file
* Loại bỏ dấu tiếng Việt
* Chuyển đổi sang chữ thường

Plugin nhẹ, không làm chậm website, và hoàn toàn miễn phí.

== Installation ==

1. Tải plugin về từ WordPress.org
2. Vào **Plugins > Add New > Upload Plugin** và tải file ZIP lên
3. Kích hoạt plugin
4. Vào **Settings > Auto Rename Image SEO** để cấu hình

Hoặc:

1. Giải nén file ZIP
2. Upload thư mục `auto-rename-image-seo` vào `/wp-content/plugins/`
3. Vào **Plugins** và kích hoạt plugin

== Frequently Asked Questions ==

= Plugin có hoạt động với tất cả loại file ảnh không? =
Có, plugin hoạt động với tất cả các loại file ảnh (JPG, PNG, GIF, WebP, v.v.)

= Plugin có làm chậm website không? =
Không, plugin chỉ hoạt động khi upload file, không ảnh hưởng đến tốc độ tải trang.

= Tôi có thể sử dụng plugin với WooCommerce không? =
Có, plugin hoạt động với tất cả các loại bài viết, bao gồm sản phẩm WooCommerce.

= Plugin có hỗ trợ đa ngôn ngữ không? =
Hiện tại plugin chỉ hỗ trợ tiếng Việt. Chúng tôi sẽ thêm hỗ trợ đa ngôn ngữ trong các phiên bản sau.

= Plugin có tự động đổi tên file đã upload trước đó không? =
Không, plugin chỉ đổi tên file khi upload mới. Để đổi tên file đã upload trước đó, bạn cần sử dụng plugin khác hoặc làm thủ công.

= Làm thế nào để gỡ cài đặt plugin? =
Vào **Plugins > Installed Plugins**, tìm plugin **Auto Rename Image SEO** và nhấn **Delete**. Tất cả cài đặt sẽ được xóa sạch.

== Screenshots ==

1. screenshot-1.png - Cài đặt plugin trong admin
2. screenshot-2.png - Ví dụ trước và sau khi đổi tên
3. screenshot-3.png - Cấu hình các tùy chọn

== Changelog ==

= 1.0.0 =
* Phiên bản đầu tiên
* Tính năng: Tự động đổi tên file ảnh khi upload
* Tùy chọn: Bật/Tắt, phương thức đổi tên, ký tự phân cách, chiều dài tối đa

= 0.9.0 =
* Phiên bản beta
