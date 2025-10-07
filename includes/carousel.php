<div class="row justify-content-center">
    <div class="col-10">
        <div id="carouselExampleDark" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="0" class="active"
                    aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="1"
                    aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#carouselExampleDark" data-bs-slide-to="2"
                    aria-label="Slide 3"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active" data-bs-interval="3000">
                    <img src="public/images/1.png" class="d-block w-100" alt="...">
                </div>
                <div class="carousel-item" data-bs-interval="3000">
                    <img src="public/images/2.png" class="d-block w-100" alt="...">
                </div>
                <div class="carousel-item" data-bs-interval="3000">
                    <img src="public/images/3.png" class="d-block w-100" alt="...">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleDark"
                data-bs-slide="prev">
                <i class="fa-solid fa-chevron-left fa-2x text-white"></i>
                <span class="visually-hidden">Previous</span>
            </button>

            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleDark"
                data-bs-slide="next">
                <i class="fa-solid fa-chevron-right fa-2x text-white"></i>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</div>

<style>
    #carouselExampleDark .carousel-indicators button {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        border: none;
        background-color: rgba(255, 255, 255, 0.6);
        transition: transform .18s ease, background-color .18s ease;
    }

    #carouselExampleDark .carousel-indicators button.active {
        background-color: #ffffff;
        transform: scale(1.25);
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        display: none;
    }

    #carouselExampleDark {
        border-radius: 20px;
        overflow: hidden;
    }
</style>