<?php
// includes/back-to-top.php
?>
<!-- Back to top button -->
<button class="back-to-top" id="backToTop" style="display: none;">
    <i class="bi bi-chevron-up"></i>
</button>

<style>
    /* Back to top button */
.back-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    background: var(--primary);
    border: none;
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    transition: var(--transition);
    color: white;
    font-size: 1.6rem;
    cursor: pointer;
}

.back-to-top:hover {
    background: var(--primary-dark);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
}

/* Animation */
.back-to-top.show {
    animation: fadeInUp 0.3s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .back-to-top {
        bottom: 20px;
        right: 20px;
        width: 45px;
        height: 45px;
        font-size: 1.4rem;
        border-radius: var(--radius-sm);
    }
}

@media (max-width: 576px) {
    .back-to-top {
        bottom: 15px;
        right: 15px;
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }
}
</style>

<script>
// Back to top button functionality
document.addEventListener('DOMContentLoaded', function() {
    const backToTopBtn = document.getElementById('backToTop');
    
    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.style.display = 'flex';
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.style.display = 'none';
                backToTopBtn.classList.remove('show');
            }
        });
        
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({ 
                top: 0, 
                behavior: 'smooth' 
            });
        });
    }
});
</script>