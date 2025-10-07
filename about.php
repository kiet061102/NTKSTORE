<?php
session_start();
include 'includes/header.php';
include 'includes/navbar.php';
?>

<!-- Giới thiệu -->
<div class="container my-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold animate__animated animate__fadeInDown">Về Chúng Tôi</h1>
        <p class="text-muted animate__animated animate__fadeInDown animate__delay-1s">
            Tìm hiểu về NTK STORE và những gì chúng tôi cung cấp
        </p>
        <hr class="animate__animated animate__fadeIn animate__delay-2s">
        <h2 class="mb-3 animate__animated animate__fadeInUp animate__delay-2s">Sứ mệnh của chúng tôi</h2>
        <p class="animate__animated animate__fadeInUp animate__delay-2s">
            NTK STORE luôn nỗ lực mang đến cho khách hàng trải nghiệm mua sắm trực tuyến tốt nhất.
            Chúng tôi cung cấp sản phẩm chất lượng, dịch vụ tận tâm và hỗ trợ nhanh chóng.
        </p>
        <p class="animate__animated animate__fadeInUp animate__delay-2s">
            Với đội ngũ chuyên nghiệp, chúng tôi cam kết đem lại sự hài lòng và tin tưởng
            cho mọi khách hàng.
        </p>
    </div>

    <!-- 3 khối giá trị -->
    <div class="row text-center">
        <div class="col-md-4 mb-4">
            <div
                class="p-4 border rounded shadow-sm h-100 hover-card animate__animated animate__fadeInUp animate__delay-1s">
                <i class="fa-solid fa-truck-fast fa-2x mb-3 text-primary"></i>
                <h5 class="mb-2">Giao hàng nhanh chóng</h5>
                <p class="text-muted">Chúng tôi đảm bảo giao hàng đúng hẹn và an toàn đến tay bạn.</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div
                class="p-4 border rounded shadow-sm h-100 hover-card animate__animated animate__fadeInUp animate__delay-2s">
                <i class="fa-solid fa-shield-halved fa-2x mb-3 text-primary"></i>
                <h5 class="mb-2">An toàn & Bảo mật</h5>
                <p class="text-muted">Thông tin khách hàng và thanh toán luôn được bảo vệ tuyệt đối.</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div
                class="p-4 border rounded shadow-sm h-100 hover-card animate__animated animate__fadeInUp animate__delay-3s">
                <i class="fa-solid fa-headset fa-2x mb-3 text-primary"></i>
                <h5 class="mb-2">Hỗ trợ tận tâm</h5>
                <p class="text-muted">Đội ngũ chăm sóc khách hàng luôn sẵn sàng giúp đỡ bạn 24/7.</p>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
        cursor: pointer;
    }

    .hover-card:hover {
        transform: translateY(-10px) scale(1.05);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        background-color: rgba(0, 123, 255, 0.05);
    }

    .hover-card i {
        transition: transform 0.3s ease, color 0.3s ease;
    }

    .hover-card:hover i {
        transform: scale(1.4) rotate(-10deg);
        color: #0d6efd;
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

<?php
include 'includes/footer.php';
?>