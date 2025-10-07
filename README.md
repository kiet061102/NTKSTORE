- Họ tên: Nguyễn Tấn Kiệt 
- MSSV: CK23V7K515 
- Mã môn: CT501E 
- Tên môn: Tiểu Luận CNTT

- Giảng viên hướng dẫn: Nguyễn Minh Trung


# C:/xampp/apache/conf/extra/httpd-vhosts.conf

<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs"
    ServerName localhost
</VirtualHost>

<VirtualHost *:80>
    DocumentRoot "D:/CTU/CT501E_Tieu_Luan/ntkstore"
    ServerName ntkstore.com

    <Directory "D:/CTU/CT501E_Tieu_Luan/ntkstore">
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
    </Directory>
</VirtualHost>
