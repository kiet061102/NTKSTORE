<?php
session_start();
include 'includes/header.php';
include 'includes/navbar.php';
?>

<!-- Giới thiệu -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary-subtle text-primary px-3 py-2 mb-3 animate__animated animate__fadeInDown">NTK
                STORE</span>
            <h1 class="fw-bold mb-3 animate__animated animate__fadeInDown">Về Chúng Tôi</h1>
            <p class="text-muted fs-5 animate__animated animate__fadeInDown animate__delay-1s">
                Tìm hiểu về NTK STORE và những gì chúng tôi cung cấp
            </p>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 animate__animated animate__fadeInLeft">
                <h2 class="fw-semibold mb-3 text-primary">Sứ mệnh của chúng tôi</h2>
                <p class="text-muted">
                    NTK STORE luôn nỗ lực mang đến cho khách hàng trải nghiệm mua sắm trực tuyến tốt nhất.
                    Chúng tôi cung cấp sản phẩm chất lượng, dịch vụ tận tâm và hỗ trợ nhanh chóng.
                </p>
                <p class="text-muted">
                    Với đội ngũ chuyên nghiệp, chúng tôi cam kết đem lại sự hài lòng và tin tưởng
                    cho mọi khách hàng.
                </p>
            </div>
            <div class="col-lg-6 animate__animated animate__fadeInRight">
                <div class="bg-white shadow-sm rounded-4 p-4">
                    <blockquote class="blockquote text-center mb-0">
                        <p class="fs-5 text-secondary fst-italic">
                            “Chúng tôi không chỉ bán sản phẩm — chúng tôi mang đến trải nghiệm mua sắm đáng tin cậy.”
                        </p>
                        <footer class="blockquote-footer mt-3">Đội ngũ <cite title="NTK STORE">NTK STORE</cite></footer>
                    </blockquote>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Giá trị cốt lõi -->
<section class="py-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-5 animate__animated animate__fadeInUp">Giá trị cốt lõi của chúng tôi</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div
                    class="value-card p-4 rounded-4 shadow-sm bg-white animate__animated animate__fadeInUp animate__delay-1s">
                    <div class="icon-wrapper mx-auto mb-3">
                        <i class="fa-solid fa-truck-fast fa-2x text-primary"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">Giao hàng nhanh chóng</h5>
                    <p class="text-muted small mb-0">Chúng tôi đảm bảo giao hàng đúng hẹn và an toàn đến tay bạn.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div
                    class="value-card p-4 rounded-4 shadow-sm bg-white animate__animated animate__fadeInUp animate__delay-2s">
                    <div class="icon-wrapper mx-auto mb-3">
                        <i class="fa-solid fa-shield-halved fa-2x text-primary"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">An toàn & Bảo mật</h5>
                    <p class="text-muted small mb-0">Thông tin khách hàng và thanh toán luôn được bảo vệ tuyệt đối.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div
                    class="value-card p-4 rounded-4 shadow-sm bg-white animate__animated animate__fadeInUp animate__delay-3s">
                    <div class="icon-wrapper mx-auto mb-3">
                        <i class="fa-solid fa-headset fa-2x text-primary"></i>
                    </div>
                    <h5 class="fw-semibold mb-2">Hỗ trợ tận tâm</h5>
                    <p class="text-muted small mb-0">Đội ngũ chăm sóc khách hàng luôn sẵn sàng giúp đỡ bạn 24/7.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .value-card {
        transition: all 0.35s ease;
    }

    .value-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    }

    .value-card:hover i {
        transform: scale(1.3);
        color: #0d6efd;
    }

    .icon-wrapper {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(13, 110, 253, 0.1);
        transition: 0.3s;
    }

    .value-card:hover .icon-wrapper {
        background-color: rgba(13, 110, 253, 0.2);
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

<?php
include 'includes/footer.php';
?>