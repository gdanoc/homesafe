<?php
include('includes/Cookies_sessions.php');
?>
<!DOCTYPE html>
<html class="wide wow-animation" lang="en">

<head>
    <title>Muebles</title>
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width height=device-height initial-scale=1.0 maximum-scale=1.0 user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="utf-8">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Work+Sans:300,400,500,700,800%7CPoppins:300,400,700">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/style.css" id="main-styles-link">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .ie-panel {
            display: none;
            background: #212121;
            padding: 10px 0;
            box-shadow: 3px 3px 5px 0 rgba(0, 0, 0, .3);
            clear: both;
            text-align: center;
            position: relative;
            z-index: 1;
        }   

        html.ie-10 .ie-panel,
        html.lt-ie-10 .ie-panel {
            display: block;
        }

        .bg_background {
            background-color: #212529;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .quantity-btn {
            background-color: #000;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            font-size: 18px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .quantity-btn:hover {
            background-color: #333;
            transform: scale(1.1);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
        }

        .quantity-input {
            width: 60px;
            height: 35px;
            text-align: center;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin: 0 5px;
            background-color: #f9f9f9;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .quantity-input:focus {
            outline: none;
            border-color: #000;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
        }

        .profile-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            width: 100%;
            max-width: 300px;
            text-align: center;
            margin: 0 auto;
        }

        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 20px;
            background-color: #ddd;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .profile-image svg {
            width: 60%;
            height: 60%;
        }

        .profile-name {
            font-size: 1.5em;
            margin-bottom: 5px;
        }

        .profile-role {
            color: #777;
            margin-bottom: 15px;
        }

        .profile-info-item {
            margin-bottom: 10px;
        }

        .profile-info-item strong {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        /* Asegurar que el navbar tenga prioridad */
        .rd-navbar-wrap {
            position: relative;
            z-index: 1000 !important;
        }

        .rd-navbar {
            z-index: 1000 !important;
        }

        /* Nueva barra de búsqueda con paleta de colores del sitio */
        .search-section {
            background: linear-gradient(135deg, #212529 0%, #444 50%, #666 100%);
            padding: 20px 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 50;
            margin-top: 0;
            border-bottom: 3px solid #FFC107;
        }

        .search-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }

        .search-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            background: white;
            border-radius: 50px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .search-input-wrapper:hover {
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.3);
            transform: translateY(-2px);
            border-color: #FFC107;
        }

        .search-input-wrapper:focus-within {
            box-shadow: 0 12px 35px rgba(255, 193, 7, 0.3);
            transform: translateY(-2px);
            border-color: #FFC107;
        }

        .search-icon-left {
            padding: 0 20px;
            color: #666;
            font-size: 20px;
            display: flex;
            align-items: center;
            transition: color 0.3s ease;
        }

        .search-input-wrapper:focus-within .search-icon-left {
            color: #FFC107;
        }

        #searchInput {
            flex: 1;
            border: none;
            outline: none;
            padding: 18px 10px;
            font-size: 16px;
            background: transparent;
            color: #333;
        }

        #searchInput::placeholder {
            color: #999;
            font-weight: 400;
        }

        .search-btn {
            background: linear-gradient(135deg, #212529 0%, #444 100%);
            border: none;
            padding: 18px 25px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .search-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #FFC107 0%, #ffdb4d 100%);
            transition: left 0.3s ease;
            z-index: -1;
        }

        .search-btn:hover::before {
            left: 0;
        }

        .search-btn:hover {
            color: #212529;
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            margin-top: 10px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 100;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            border: 2px solid #FFC107;
        }

        .search-results.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .search-results::-webkit-scrollbar {
            width: 8px;
        }

        .search-results::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .search-results::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #212529 0%, #FFC107 100%);
            border-radius: 10px;
        }

        .search-results::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #FFC107 0%, #212529 100%);
        }

        .search-result-item {
            padding: 15px 20px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            overflow: hidden;
        }

        .search-result-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #212529 0%, #444 100%);
            transition: left 0.3s ease;
            z-index: -1;
        }

        .search-result-item:hover::before {
            left: 0;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item:hover {
            color: white;
            transform: translateX(5px);
        }

        .search-result-item:hover .search-result-icon {
            color: #FFC107;
        }

        .search-result-item:first-child {
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .search-result-item:last-child {
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
        }

        .search-result-icon {
            font-size: 16px;
            opacity: 0.7;
            color: #666;
            transition: color 0.3s ease;
        }

        .no-results {
            padding: 20px;
            text-align: center;
            color: #666;
            font-style: italic;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .no-results i {
            color: #FFC107;
            font-size: 1.2em;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .search-section {
                padding: 15px 0;
            }

            .search-container {
                margin: 0 15px;
            }

            .search-input-wrapper {
                border-radius: 25px;
            }

            #searchInput {
                padding: 15px 10px;
                font-size: 15px;
            }

            .search-icon-left {
                padding: 0 15px;
                font-size: 18px;
            }

            .search-btn {
                padding: 15px 20px;
                font-size: 14px;
            }

            .search-result-item {
                padding: 12px 15px;
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .search-section {
                padding: 12px 0;
            }

            .search-container {
                margin: 0 10px;
            }

            #searchInput {
                padding: 12px 8px;
                font-size: 14px;
            }

            .search-icon-left {
                padding: 0 12px;
                font-size: 16px;
            }

            .search-btn {
                padding: 12px 15px;
                font-size: 13px;
            }

            .search-btn span {
                display: none;
            }
        }

        /* Animación de carga para los resultados */
        .search-loading {
            padding: 20px;
            text-align: center;
            color: #212529;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .search-loading::after {
            content: '';
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #212529;
            border-radius: 50%;
            border-top-color: #FFC107;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive para tarjetas de muebles */
        .furniture-card {
            padding: 12px;
            border: 2px solid #444;
            border-radius: 10px;
            text-align: center;
            width: 100%;
            max-width: 280px;
            margin: 0 auto 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
            top: 0;
        }

        .furniture-card:hover {
            top: -5px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .furniture-image {
            width: 100%;
            max-width: 180px;
            height: 180px;
            border-radius: 8px;
            object-fit: cover;
            cursor: pointer;
            margin: 0 auto;
            display: block;
        }

        /* Media Queries para Responsive */
        @media (max-width: 992px) {
            .furniture-card {
                max-width: 250px;
            }

            .furniture-image {
                max-width: 160px;
                height: 160px;
            }

            .profile-container {
                width: 90%;
                max-width: 280px;
            }
        }

        @media (max-width: 768px) {
            .furniture-card {
                max-width: 220px;
                padding: 10px;
            }

            .furniture-image {
                max-width: 140px;
                height: 140px;
            }

            .quantity-btn {
                width: 30px;
                height: 30px;
                font-size: 16px;
            }

            .quantity-input {
                width: 50px;
                height: 30px;
                font-size: 14px;
            }

            .profile-container {
                padding: 15px;
            }

            .profile-name {
                font-size: 1.3em;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding-left: 10px;
                padding-right: 10px;
            }

            .furniture-card {
                max-width: 200px;
                padding: 8px;
                margin-bottom: 15px;
            }

            .furniture-image {
                max-width: 120px;
                height: 120px;
            }

            .post-modern-title {
                font-size: 15px !important;
            }

            .quantity-btn {
                width: 28px;
                height: 28px;
                font-size: 14px;
            }

            .quantity-input {
                width: 45px;
                height: 28px;
                font-size: 12px;
            }

            .btn-sm {
                font-size: 12px;
                padding: 5px 10px;
            }

            .profile-container {
                width: 95%;
                padding: 10px;
            }

            .profile-image {
                width: 80px;
                height: 80px;
            }

            .profile-name {
                font-size: 1.2em;
            }
        }

        @media (max-width: 400px) {
            .furniture-card {
                max-width: 180px;
                padding: 6px;
            }

            .furniture-image {
                max-width: 100px;
                height: 100px;
            }

            .post-modern-title {
                font-size: 14px !important;
            }

            .quantity-btn {
                width: 25px;
                height: 25px;
                font-size: 12px;
            }

            .quantity-input {
                width: 40px;
                height: 25px;
                font-size: 11px;
            }

            .profile-container {
                padding: 8px;
            }

            .profile-image {
                width: 70px;
                height: 70px;
            }
        }

        .heart-link .fa-heart {
            font-size: 1.5em;
        }

        .discount-badge-top {
            position: absolute;
            top: 8px;
            right: 0;
            background-color: #FFC107;
            color: #333;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 4px;
            z-index: 2;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .discount-badge-top .fa-arrow-down {
            color: #DC3545;
        }

        .discount-percentage-badge {
            background-color: #FFC107;
            color: #333;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .review-btn {
            background-color: #212529;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: absolute;
            top: 8px;
            right: 8px;
            z-index: 3;
            padding: 0;
            outline: none;
        }

        .review-btn:hover {
            background-color: #444;
            transform: scale(1.1);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
        }

        .review-btn i {
            color: #FFC107;
        }

        .star-rating {
            display: inline-block;
            font-size: 2em;
            cursor: pointer;
            margin-bottom: 15px;
        }

        .star-rating input[type="radio"] {
            display: none;
        }

        .star-rating label {
            color: #ccc;
            float: right;
            transition: color 0.2s ease-in-out;
        }

        .star-rating label:hover,
        .star-rating label:hover~label,
        .star-rating input[type="radio"]:checked~label {
            color: #FFC107;
        }

        .star-rating label:before {
            content: '\2605';
            padding: 0 2px;
        }

        .review-comment-counter {
            font-size: 0.8em;
            color: #777;
            text-align: right;
            margin-top: 5px;
        }

        .reviews-container {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .review-item {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #fefefe;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .review-item .font-weight-bold {
            color: #212529;
        }

        .review-item p {
            font-size: 0.9em;
            color: #555;
        }

        .review-item small {
            font-size: 0.75em;
            color: #888;
        }

        .tooltip-inner {
            background-color: #212529 !important;
            color: #FFC107 !important;
            font-weight: bold;
            font-size: 13px;
            border-radius: 6px;
            border: 2px solid #FFC107;
            padding: 8px 12px;
        }

        .bs-tooltip-top .arrow::before,
        .bs-tooltip-auto[x-placement^="top"] .arrow::before {
            border-top-color: #212529 !important;
        }

        .bs-tooltip-bottom .arrow::before,
        .bs-tooltip-auto[x-placement^="bottom"] .arrow::before {
            border-bottom-color: #212529 !important;
        }

        .bs-tooltip-left .arrow::before,
        .bs-tooltip-auto[x-placement^="left"] .arrow::before {
            border-left-color: #212529 !important;
        }

        .bs-tooltip-right .arrow::before,
        .bs-tooltip-auto[x-placement^="right"] .arrow::before {
            border-right-color: #212529 !important;
        }

        .swal2-container {
            z-index: 20000 !important;
        }

        .average-rating-container {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 5px !important;
            margin: 8px 0 !important;
            flex-wrap: wrap;
        }

        @media (max-width: 576px) {
            .average-rating-container {
                gap: 3px !important;
                margin: 6px 0 !important;
            }

            .average-rating-container i {
                font-size: 12px !important;
            }

            .average-rating-container span {
                font-size: 10px !important;
            }
        }

        @media (max-width: 400px) {
            .average-rating-container {
                gap: 2px !important;
                margin: 4px 0 !important;
            }

            .average-rating-container i {
                font-size: 11px !important;
            }

            .average-rating-container span {
                font-size: 9px !important;
            }
        }

        /* Mejoras responsive para productos populares */
        .popular-item {
            padding: 15px !important;
            border: 3px solid #FFC107 !important;
            border-radius: 15px !important;
            text-align: center !important;
            width: 100% !important;
            max-width: 320px !important;
            margin: 0 auto 20px !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 8px 20px rgba(255, 193, 7, 0.2) !important;
            position: relative !important;
            background: white !important;
            min-height: 480px !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
        }

        .popular-item:hover {
            transform: translateY(-8px) !important;
            box-shadow: 0 15px 30px rgba(255, 193, 7, 0.3) !important;
        }

        /* Badge de Popular responsive */
        .popular-badge {
            position: absolute !important;
            top: -10px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            background: linear-gradient(135deg, #FFC107, #ffdb4d) !important;
            color: #212529 !important;
            padding: 8px 20px !important;
            border-radius: 20px !important;
            font-size: 12px !important;
            font-weight: bold !important;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
            z-index: 5 !important;
            white-space: nowrap !important;
        }

        /* Contenedor de imagen responsive */
        .popular-item-image-container {
            position: relative !important;
            display: inline-block !important;
            margin: 15px auto 10px !important;
        }

        .popular-item img {
            width: 200px !important;
            height: 200px !important;
            border-radius: 10px !important;
            object-fit: cover !important;
            cursor: pointer !important;
            border: 2px solid #FFC107 !important;
            transition: transform 0.3s ease !important;
        }

        .popular-item img:hover {
            transform: scale(1.05) !important;
        }

        /* Título responsive */
        .popular-item .post-modern-title {
            margin: 15px 0 10px !important;
            font-size: 18px !important;
            font-weight: bold !important;
            color: #212529 !important;
            line-height: 1.3 !important;
            min-height: 48px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* Rating container responsive */
        .popular-item .popular-rating-container {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            margin: 12px 0 !important;
            padding: 10px !important;
            background: #f8f9fa !important;
            border-radius: 8px !important;
            border: 1px solid #FFC107 !important;
            min-height: 45px !important;
        }

        .popular-item .popular-rating-container i {
            font-size: 16px !important;
            color: #FFC107 !important;
        }

        .popular-item .popular-rating-container span {
            font-size: 14px !important;
            color: #212529 !important;
            font-weight: bold !important;
        }

        /* Precios responsive */
        .popular-item .popular-price {
            font-size: 18px !important;
            color: #212529 !important;
            margin: 8px 0 !important;
            font-weight: bold !important;
            min-height: 25px !important;
        }

        .popular-item .popular-price .original-price {
            text-decoration: line-through !important;
            color: #888 !important;
            margin-left: 8px !important;
            font-weight: normal !important;
        }

        /* Botones responsive */
        .popular-item .btn {
            width: 100% !important;
            max-width: 100% !important;
            margin: 10px 0 0 0 !important;
            padding: 12px 8px !important;
            font-size: 14px !important;
            font-weight: bold !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            box-sizing: border-box !important;
            background: linear-gradient(135deg, #212529, #444) !important;
            border: 2px solid #FFC107 !important;
            color: white !important;
            transition: all 0.3s ease !important;
        }

        .popular-item .btn:hover {
            background: linear-gradient(135deg, #FFC107, #ffdb4d) !important;
            color: #212529 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4) !important;
        }

        /* Controles de cantidad responsive */
        .popular-quantity-controls {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px !important;
            margin: 15px 0 !important;
        }

        .popular-quantity-controls .quantity-btn {
            width: 35px !important;
            height: 35px !important;
            font-size: 18px !important;
        }

        .popular-quantity-controls .quantity-input {
            width: 60px !important;
            height: 35px !important;
            font-size: 16px !important;
        }

        /* Media Queries para tablets */
        @media (max-width: 992px) {
            .popular-item {
                max-width: 280px !important;
                padding: 12px !important;
                min-height: 450px !important;
            }

            .popular-item img {
                width: 180px !important;
                height: 180px !important;
            }

            .popular-item .post-modern-title {
                font-size: 16px !important;
                min-height: 40px !important;
            }

            .popular-badge {
                padding: 6px 16px !important;
                font-size: 11px !important;
            }

            .popular-item .popular-rating-container i {
                font-size: 15px !important;
            }

            .popular-item .popular-rating-container span {
                font-size: 13px !important;
            }
        }

        /* Media Queries para móviles */
        @media (max-width: 768px) {
            .popular-item {
                max-width: 250px !important;
                padding: 10px !important;
                min-height: 420px !important;
                margin-bottom: 25px !important;
            }

            .popular-item img {
                width: 160px !important;
                height: 160px !important;
            }

            .popular-item .post-modern-title {
                font-size: 15px !important;
                min-height: 36px !important;
            }

            .popular-badge {
                padding: 5px 12px !important;
                font-size: 10px !important;
                top: -8px !important;
            }

            .popular-item .popular-price {
                font-size: 16px !important;
            }

            .popular-item .btn {
                padding: 10px 6px !important;
                font-size: 13px !important;
            }

            .popular-quantity-controls .quantity-btn {
                width: 30px !important;
                height: 30px !important;
                font-size: 16px !important;
            }

            .popular-quantity-controls .quantity-input {
                width: 50px !important;
                height: 30px !important;
                font-size: 14px !important;
            }

            .popular-item .popular-rating-container {
                padding: 8px !important;
                gap: 6px !important;
            }

            .popular-item .popular-rating-container i {
                font-size: 14px !important;
            }

            .popular-item .popular-rating-container span {
                font-size: 12px !important;
            }
        }

        /* Media Queries para móviles pequeños */
        @media (max-width: 576px) {
            .popular-item {
                max-width: 220px !important;
                padding: 8px !important;
                min-height: 400px !important;
                margin-bottom: 20px !important;
            }

            .popular-item img {
                width: 140px !important;
                height: 140px !important;
            }

            .popular-item .post-modern-title {
                font-size: 14px !important;
                min-height: 32px !important;
            }

            .popular-badge {
                padding: 4px 10px !important;
                font-size: 9px !important;
                top: -6px !important;
            }

            .popular-item .popular-price {
                font-size: 15px !important;
            }

            .popular-item .btn {
                padding: 8px 4px !important;
                font-size: 12px !important;
            }

            .popular-quantity-controls .quantity-btn {
                width: 28px !important;
                height: 28px !important;
                font-size: 14px !important;
            }

            .popular-quantity-controls .quantity-input {
                width: 45px !important;
                height: 28px !important;
                font-size: 12px !important;
            }

            .popular-item .popular-rating-container {
                padding: 6px !important;
                gap: 4px !important;
                margin: 10px 0 !important;
            }

            .popular-item .popular-rating-container i {
                font-size: 13px !important;
            }

            .popular-item .popular-rating-container span {
                font-size: 11px !important;
            }
        }

        /* Media Queries para pantallas muy pequeñas */
        @media (max-width: 400px) {
            .popular-item {
                max-width: 190px !important;
                padding: 6px !important;
                min-height: 380px !important;
                margin-bottom: 15px !important;
            }

            .popular-item img {
                width: 120px !important;
                height: 120px !important;
            }

            .popular-item .post-modern-title {
                font-size: 13px !important;
                min-height: 28px !important;
            }

            .popular-badge {
                padding: 3px 8px !important;
                font-size: 8px !important;
                top: -5px !important;
            }

            .popular-item .popular-price {
                font-size: 14px !important;
            }

            .popular-item .btn {
                padding: 6px 4px !important;
                font-size: 11px !important;
            }

            /* Ocultar texto del botón en pantallas muy pequeñas, solo mostrar ícono */
            .popular-item .btn .btn-text {
                display: none !important;
            }

            .popular-quantity-controls .quantity-btn {
                width: 25px !important;
                height: 25px !important;
                font-size: 12px !important;
            }

            .popular-quantity-controls .quantity-input {
                width: 40px !important;
                height: 25px !important;
                font-size: 11px !important;
            }

            .popular-item .popular-rating-container {
                padding: 4px !important;
                gap: 2px !important;
                margin: 8px 0 !important;
            }

            .popular-item .popular-rating-container i {
                font-size: 12px !important;
            }

            .popular-item .popular-rating-container span {
                font-size: 10px !important;
            }
        }

        /* Título de la sección responsive */
        @media (max-width: 768px) {
            .section .container h3 {
                font-size: 24px !important;
            }

            .section .container h3 i {
                font-size: 20px !important;
            }
        }

        @media (max-width: 576px) {
            .section .container h3 {
                font-size: 20px !important;
                margin-bottom: 30px !important;
            }

            .section .container h3 i {
                font-size: 18px !important;
                margin: 0 5px !important;
            }

            .section .container p {
                font-size: 14px !important;
            }
        }

        /* Grid responsive para productos populares */
        @media (max-width: 1200px) {
            .row-lg-50.row-35 .col-lg-4 {
                max-width: 50% !important;
                flex: 0 0 50% !important;
            }
        }

        @media (max-width: 768px) {
            .row-lg-50.row-35 .col-lg-4 {
                max-width: 100% !important;
                flex: 0 0 100% !important;
            }
        }
    </style>
    
</head>

<body>
    <?php include 'modals.php'; ?>

    <!-- Modal para añadir reseña -->
    <div class="modal fade" id="reviewModal" tabindex="-1" role="dialog" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewModalLabel">Añadir Reseña para <span id="reviewFurnitureName"></span></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="reviewForm">
                        <input type="hidden" id="reviewFurnitureId" name="mueble_id">
                        <div class="form-group text-center">
                            <label for="rating" class="d-block mb-2">Tu Calificación:</label>
                            <div class="star-rating">
                                <input type="radio" id="star5" name="rating" value="5" /><label for="star5" title="Excelente"></label>
                                <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="Muy Bien"></label>
                                <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="Bien"></label>
                                <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="Mala"></label>
                                <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="Muy Mala"></label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comment">Mensaje (máx. 255 caracteres):</label>
                            <textarea class="form-control" id="reviewComment" name="comment" rows="3" maxlength="255" onkeyup="updateCharCounter(this, 'charCounter')" required></textarea>
                            <div id="charCounter" class="review-comment-counter">0/255</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="submitReview()">Enviar Reseña</button>
                </div>
            </div>
        </div>
    </div>

    <div class="page">
        <!-- Page Header-->
        <header class="section novi-background page-header">
            <!-- RD Navbar-->
            <div class="rd-navbar-wrap">
                <nav class="rd-navbar rd-navbar-corporate" data-layout="rd-navbar-fixed" data-sm-layout="rd-navbar-fixed" data-md-layout="rd-navbar-fixed" data-md-device-layout="rd-navbar-fixed" data-lg-layout="rd-navbar-static" data-lg-device-layout="rd-navbar-static" data-lg-stick-up="true" data-lg-stick-up-offset="118px" data-xl-layout="rd-navbar-static" data-xl-device-layout="rd-navbar-static" data-xl-stick-up="true" data-xl-stick-up-offset="118px" data-xxl-layout="rd-navbar-static" data-xxl-device-layout="rd-navbar-static" data-xxl-stick-up-offset="118px" data-xxl-stick-up="true">
                    <div class="rd-navbar-aside-outer">
                        <div class="rd-navbar-aside">
                            <!-- RD Navbar Panel-->
                            <div class="rd-navbar-panel">
                                <!-- RD Navbar Toggle-->
                                <button class="rd-navbar-toggle" data-rd-navbar-toggle="#rd-navbar-nav-wrap-1"><span></span></button>
                                <!-- RD Navbar Brand--><a class="rd-navbar-brand" href="index.php"><img src="images/logo-default-151x44.png" alt="" width="151" height="44" srcset="images/logo-default-151x44.png 2x" /></a>
                            </div>
                            <div class="rd-navbar-collapse">
                                <button class="rd-navbar-collapse-toggle rd-navbar-fixed-element-1" data-rd-navbar-toggle="#rd-navbar-collapse-content-1"><span></span></button>
                                <div class="rd-navbar-collapse-content" id="rd-navbar-collapse-content-1">
                                    <article class="unit align-items-center">
                                        <div class="unit-left"><span class="icon novi-icon icon-md icon-modern mdi mdi-phone"></span></div>
                                        <div class="unit-body">
                                            <ul class="list-0">
                                                <li><a class="link-default" href="tel:#">1-800-1234-567</a></li>
                                                <li><a class="link-default" href="tel:#">1-800-8763-765</a></li>
                                            </ul>
                                        </div>
                                    </article>
                                    <article class="unit align-items-center">
                                        <div class="unit-left"><span class="icon novi-icon icon-md icon-modern mdi mdi-map-marker"></span></div>
                                        <div class="unit-body"><a class="link-default" href="tel:#">5 Calle Ote. 1-1,<br>Santa Tecla, El Salvador</a></div>
                                    </article>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="rd-navbar-main-outer custom">
                        <div class="rd-navbar-main">
                            <div class="rd-navbar-nav-wrap" id="rd-navbar-nav-wrap-1">
                                <!-- RD Navbar Nav-->
                                <ul class="rd-navbar-nav">
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="index.php">Inicio</a></li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="properties.php">Propiedades</a></li>
                                    <li class="rd-nav-item active"><a class="rd-nav-link" href="furniture.php">Muebles</a></li>
                                    <li class="rd-nav-item"><a class="rd-nav-link" href="about-us.php">Sobre nosotros</a></li>
                                    <?php if (!isset($_SESSION['email'])): ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#loginModal">Iniciar Sesión</a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#RegisterModal">Registrarse</a></li>
                                    <?php else: ?>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="logout.p.php">Cerrar Sesión</a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="#" data-toggle="modal" data-target="#PerfilModal">Sesión actual: <?php echo htmlspecialchars($username); ?></a></li>
                                        <li class="rd-nav-item"><a class="rd-nav-link" href="mostrarcarrito.php"><i class="fa fa-shopping-cart" style="font-size: 1.5em;"></i></a></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </header>
        <br>
        <br>
        <!-- Nueva Sección de Búsqueda Mejorada -->
        <section class="search-section">
            <div class="search-container">
                <div class="search-input-wrapper">
                    <div class="search-icon-left">
                        <i class="fa fa-search"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Buscar muebles por nombre..." onkeyup="searchFurniture()" autocomplete="off">
                    <button class="search-btn" onclick="performSearch()">
                        <i class="fa fa-search"></i>
                        <span>Buscar</span>
                    </button>
                </div>
                <div class="search-results" id="searchResults"></div>
            </div>
        </section>



        <?php
        
        $servername = "localhost";
        $username = "postgres";
        $password = "TU_PASSWORD_DE_BASE_DE_DATOS";
        $dbname = "homesafe";

        
        $conn = pg_connect("host=$servername dbname=$dbname user=$username password=$password");

        
        if (!$conn) {
            die("Conexión fallida: " . pg_last_error());
        }

        
        $query = "SELECT 
        m.id, 
        m.nombre, 
        m.descripcion, 
        m.precio, 
        m.cantidad, 
        m.imagen, 
        m.descuento,
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(r.id) as total_reviews
    FROM muebles m 
    LEFT JOIN reviews r ON m.id = r.mueble_id 
    GROUP BY m.id, m.nombre, m.descripcion, m.precio, m.cantidad, m.imagen, m.descuento
    ORDER BY m.id";
        $result = pg_query($conn, $query);
        ?>
        <?php
        
        $popular_query = "SELECT 
    m.id, 
    m.nombre, 
    m.descripcion, 
    m.precio, 
    m.cantidad, 
    m.imagen, 
    m.descuento,
    COALESCE(AVG(r.rating), 0) as avg_rating,
    COUNT(r.id) as total_reviews
FROM muebles m 
LEFT JOIN reviews r ON m.id = r.mueble_id 
GROUP BY m.id, m.nombre, m.descripcion, m.precio, m.cantidad, m.imagen, m.descuento
HAVING COUNT(r.id) > 0 
ORDER BY avg_rating DESC, total_reviews DESC 
LIMIT 3";
        $popular_result = pg_query($conn, $popular_query);
        ?>

        <!-- Sección de Productos Más Populares -->
        <?php if (pg_num_rows($popular_result) > 0): ?>
            <section class="section novi-background section-md text-center" style="background-color: #f8f9fa; padding: 60px 0;">
                <div class="container">
                    <h3 class="text-uppercase font-weight-bold wow-outer" style="margin-bottom: 40px;">
                        <span class="wow slideInDown" style="color: #212529;">
                            <i class="fa fa-star" style="color: #FFC107; margin-right: 10px;"></i>
                            Productos Más Populares
                            <i class="fa fa-star" style="color: #FFC107; margin-left: 10px;"></i>
                        </span>
                    </h3>
                    <p class="text-muted mb-5">Los muebles mejor valorados por nuestros clientes</p>

                    <div class="row row-lg-50 row-35">
                        <?php while ($popular_row = pg_fetch_assoc($popular_result)):
                            $precio_original = (float)$popular_row['precio'];
                            $descuento_valor = isset($popular_row['descuento']) ? (float)$popular_row['descuento'] : 0.00;
                            $precio_con_descuento = $precio_original;
                            if ($descuento_valor > 0) {
                                $precio_con_descuento = $precio_original * (1 - ($descuento_valor / 100));
                            }
                        ?>
                            <div class="col-md-6 col-lg-4 wow-outer mb-4">
                                <article class="post-modern popular-item wow fadeIn">
                                    <!-- Badge de Popular -->
                                    <div class="popular-badge">
                                        <i class="fa fa-crown" style="margin-right: 5px;"></i> POPULAR
                                    </div>

                                    <div class="popular-item-image-container">
                                        <?php if ($descuento_valor > 0): ?>
                                            <div class="discount-badge-top">
                                                <i class="fa fa-arrow-down"></i> Producto con descuento
                                            </div>
                                        <?php endif; ?>

                                        <a href="javascript:void(0);" onclick="loadFurnitureDetails(<?php echo $popular_row['id']; ?>); $('#detailsModal').modal('show');" class="post-modern-media">
                                            <img src="data:image/jpeg;base64,<?php echo base64_encode(pg_unescape_bytea($popular_row['imagen'])); ?>" alt="<?php echo $popular_row['nombre']; ?>" />
                                        </a>

                                        <?php
                                        $is_favorited = false;
                                        if (isset($_SESSION['email'])) {
                                            $conn_fav = pg_connect("host=localhost port=5432 dbname=homesafe user=postgres password=Info2025/*-");
                                            $fav_stmt = pg_query_params($conn_fav, "SELECT id FROM favoritos WHERE mail_user = $1 AND mueble_id = $2", [$_SESSION['email'], $popular_row['id']]);
                                            $is_favorited = pg_num_rows($fav_stmt) > 0;
                                            pg_close($conn_fav);
                                        }
                                        ?>

                                        <?php if (isset($_SESSION['email'])): ?>
                                            <button type="button" onclick="event.stopPropagation(); toggleFavorite(<?php echo $popular_row['id']; ?>, this);" class="favorite-btn" style="position: absolute; bottom: 8px; right: 8px; background: rgba(255, 255, 255, 0.95); border: 2px solid rgba(231, 76, 60, 0.3); border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); cursor: pointer; transition: all 0.3s ease; z-index: 3; padding: 0; outline: none;">
                                                <i class="<?php echo $is_favorited ? 'fa fa-heart' : 'fa fa-heart-o'; ?>" style="color: <?php echo $is_favorited ? 'rgb(231, 76, 60)' : 'rgb(221, 90, 76)'; ?>; font-size: 18px;"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <h4 class="post-modern-title">
                                        <?php echo htmlspecialchars($popular_row['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                    </h4>

                                    <!-- Rating destacado -->
                                    <div class="popular-rating-container">
                                        <?php
                                        $avg_rating = floatval($popular_row['avg_rating']);
                                        $total_reviews = intval($popular_row['total_reviews']);
                                        $rounded_rating = round($avg_rating * 2) / 2;
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rounded_rating) {
                                                echo '<i class="fa fa-star"></i>';
                                            } elseif ($i - 0.5 == $rounded_rating) {
                                                echo '<i class="fa fa-star-half-o"></i>';
                                            } else {
                                                echo '<i class="fa fa-star-o" style="color: #ddd;"></i>';
                                            }
                                        }
                                        ?>
                                        <span>
                                            <?php echo number_format($avg_rating, 1); ?> (<?php echo $total_reviews; ?> reseña<?php echo $total_reviews != 1 ? 's' : ''; ?>)
                                        </span>
                                    </div>

                                    <p class="popular-price">
                                        $<?php echo htmlspecialchars(number_format($precio_con_descuento, 2), ENT_QUOTES, 'UTF-8'); ?>
                                        <?php if ($descuento_valor > 0): ?>
                                            <span class="original-price">
                                                $<?php echo htmlspecialchars(number_format($precio_original, 2), ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </p>

                                    <?php if ($descuento_valor > 0): ?>
                                        <div class="discount-percentage-badge">
                                            - <?php echo htmlspecialchars($descuento_valor, ENT_QUOTES, 'UTF-8'); ?>%
                                        </div>
                                    <?php endif; ?>

                                    <p style="font-size: 14px; color: #666;">Disponible: <?php echo htmlspecialchars($popular_row['cantidad'], ENT_QUOTES, 'UTF-8'); ?> unidades</p>

                                    <div class="popular-quantity-controls">
                                        <button type="button" onclick="decreaseQuantity('popular-quantity-<?php echo $popular_row['id']; ?>')" class="quantity-btn" <?php echo ($popular_row['cantidad'] == 0) ? 'disabled' : ''; ?>>-</button>
                                        <input type="number" id="popular-quantity-<?php echo $popular_row['id']; ?>" name="quantity" value="1" min="1" max="<?php echo (int)$popular_row['cantidad']; ?>" readonly class="quantity-input" <?php echo ($popular_row['cantidad'] == 0) ? 'disabled' : ''; ?>>
                                        <button type="button" onclick="increaseQuantity('popular-quantity-<?php echo $popular_row['id']; ?>')" class="quantity-btn" <?php echo ($popular_row['cantidad'] == 0) ? 'disabled' : ''; ?>>+</button>
                                    </div>

                                    <a href="javascript:void(0);" onclick="<?php echo ($popular_row['cantidad'] == 0) ? 'noStockAlert(); return false;' : "addToCart({$popular_row['id']}, document.getElementById('popular-quantity-{$popular_row['id']}').value, {$popular_row['cantidad']})"; ?>" class="btn btn-primary btn-sm">
                                        <i class="fa fa-shopping-cart"></i> <span class="btn-text">Agregar al carrito</span>
                                    </a>
                                </article>
                            </div>
                        <?php endwhile; ?>

                    </div>

                    <div class="mt-4">
                        <p style="color: #666; font-style: italic;">
                            <i class="fa fa-info-circle" style="color: #FFC107;"></i>
                            Productos seleccionados basados en las mejores calificaciones de nuestros clientes
                        </p>
                    </div>
                </div>
            </section>
        <?php endif; ?>


        <section class="section novi-background section-md text-center">
            <div class="container">
                <h3 class="text-uppercase font-weight-bold wow-outer">
                    <br>
                    <span class="wow slideInDown">Muebles disponibles</span>
                </h3>
                <div class="row row-lg-50 row-35 offset-top-2">
                    <?php
                    
                    if (pg_num_rows($result) > 0) {
                        
                        while ($row = pg_fetch_assoc($result)) {
                            $precio_original = (float)$row['precio'];
                            
                            $descuento_valor = isset($row['descuento']) ? (float)$row['descuento'] : 0.00;
                            $precio_con_descuento = $precio_original;
                            if ($descuento_valor > 0) {
                                
                                $precio_con_descuento = $precio_original * (1 - ($descuento_valor / 100));
                            }
                    ?>
                            <div class="col-md-4 col-lg-3 wow-outer" style="padding-top: 8px; padding-bottom: 20px; margin-bottom: 10px;">
                                <article class="post-modern wow fadeIn" style="padding: 12px; border: 2px solid #444; border-radius: 10px; text-align: center; max-width: 280px; margin: 0 auto; transition: all 0.3s ease; box-shadow: 0 4px 8px rgba(0,0,0,0.1); position: relative; top: 0;" onmouseover="this.style.top='-5px'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.2)';" onmouseout="this.style.top='0'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.1)';">
                                    <!-- Contenedor relativo para la imagen y el botón de corazón -->
                                    <div style="position: relative; display: inline-block;">
                                        <?php if ($descuento_valor > 0) : ?>
                                            <div class="discount-badge-top">
                                                <i class="fa fa-arrow-down"></i> Producto con descuento
                                            </div>
                                        <?php endif; ?>
                                        <a href="javascript:void(0);" onclick="loadFurnitureDetails(<?php echo $row['id']; ?>); $('#detailsModal').modal('show');" class="post-modern-media">
                                            <img src="data:image/jpeg;base64,<?php echo base64_encode(pg_unescape_bytea($row['imagen'])); ?>" alt="<?php echo $row['nombre']; ?>" style="width: 180px; height: 180px; border-radius: 8px; object-fit: cover; cursor: pointer;" />
                                        </a>
                                        <?php
                                        $is_favorited = false;
                                        if (isset($_SESSION['email'])) {
                                            
                                            $conn_fav = pg_connect("host=localhost port=5432 dbname=homesafe user=postgres password=Info2025/*-");
                                            $fav_stmt = pg_query_params($conn_fav, "SELECT id FROM favoritos WHERE mail_user = $1 AND mueble_id = $2", [$_SESSION['email'], $row['id']]);
                                            $is_favorited = pg_num_rows($fav_stmt) > 0;
                                            pg_close($conn_fav);
                                        }
                                        ?>
                                        <?php if (isset($_SESSION['email'])) : ?>
                                            <button type="button" onclick="event.stopPropagation(); toggleFavorite(<?php echo $row['id']; ?>, this);" class="favorite-btn" style="position: absolute; bottom: 8px; right: 8px; background: rgba(255, 255, 255, 0.95); border: 2px solid rgba(231, 76, 60, 0.3); border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); cursor: pointer; transition: all 0.3s ease; z-index: 3; padding: 0; outline: none;" onmouseover="this.style.background='rgba(255, 255, 255, 1)'; this.style.transform='scale(1.1)'; this.style.boxShadow='0 6px 16px rgba(0, 0, 0, 0.2)'; this.style.borderColor='rgba(231, 76, 60, 0.6)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.95)'; this.style.transform='scale(1)'; this.style.boxShadow='0 4px 12px rgba(0, 0, 0, 0.15)'; this.style.borderColor='rgba(231, 76, 60, 0.3)';">
                                                <i class="<?php echo $is_favorited ? 'fa fa-heart' : 'fa fa-heart-o'; ?>" style="color: <?php echo $is_favorited ? 'rgb(231, 76, 60)' : 'rgb(221, 90, 76)'; ?>; font-size: 18px; line-height: 1; display: flex; align-items: center; justify-content: center; margin: 0; padding: 0; margin-right: 4px;"></i>
                                            </button>
                                            <!-- Botón de Añadir Reseña -->
                                            <button type="button" onclick="event.stopPropagation(); openReviewModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8'); ?>');" class="review-btn" data-toggle="tooltip" data-placement="top" title="Haz clic para añadir una reseña" style="position: absolute; bottom: 8px; left: 8px; background-color: #212529; color: #FFC107; border: 2px solid #FFC107;">
                                                <i class="fa fa-star"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <h4 class="post-modern-title" style="margin-top: 10px; font-size: 17px; font-weight: bold;">
                                        <?php echo htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                    </h4>
                                    <!-- AGREGAR AQUÍ EL PROMEDIO DE RESEÑAS -->
                                    <?php
                                    $avg_rating = floatval($row['avg_rating']);
                                    $total_reviews = intval($row['total_reviews']);
                                    if ($total_reviews > 0) {
                                    ?>
                                        <div class="average-rating-container" style="display: flex; align-items: center; justify-content: center; gap: 5px; margin: 8px 0;">
                                            <?php
                                            $rounded_rating = round($avg_rating * 2) / 2;
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $rounded_rating) {
                                                    echo '<i class="fa fa-star" style="color: #FFC107; font-size: 14px;"></i>';
                                                } elseif ($i - 0.5 == $rounded_rating) {
                                                    echo '<i class="fa fa-star-half-o" style="color: #FFC107; font-size: 14px;"></i>';
                                                } else {
                                                    echo '<i class="fa fa-star-o" style="color: #ddd; font-size: 14px;"></i>';
                                                }
                                            }
                                            ?>
                                            <span style="font-size: 12px; color: #666; margin-left: 4px;">
                                                (<?php echo number_format($avg_rating, 1); ?>) <?php echo $total_reviews; ?> reseña<?php echo $total_reviews != 1 ? 's' : ''; ?>
                                            </span>
                                        </div>
                                    <?php } else { ?>
                                        <div class="average-rating-container" style="display: flex; align-items: center; justify-content: center; gap: 5px; margin: 8px 0;">
                                            <span style="font-size: 12px; color: #999; font-style: italic;">Sin reseñas aún</span>
                                        </div>
                                    <?php } ?>
                                    <!-- FIN DEL PROMEDIO DE RESEÑAS -->

                                    <p style="font-size: 16px; color: #444; margin-bottom: 5px; font-weight: bold;">
                                        $<?php echo htmlspecialchars(number_format($precio_con_descuento, 2), ENT_QUOTES, 'UTF-8'); ?>
                                        <?php if ($descuento_valor > 0) : ?>
                                            <span style="text-decoration: line-through; color: #888; margin-left: 8px; font-weight: normal;">
                                                $<?php echo htmlspecialchars(number_format($precio_original, 2), ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                    <?php if ($descuento_valor > 0) : ?>
                                        <div class="discount-percentage-badge">
                                            - <?php echo htmlspecialchars($descuento_valor, ENT_QUOTES, 'UTF-8'); ?>%
                                        </div>
                                    <?php endif; ?>
                                    <p style="font-size: 14px;">Cantidad: <?php echo htmlspecialchars($row['cantidad'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p style="font-size: 12px; color: #666; margin-top: 5px; max-height: 60px; overflow: hidden;">
                                        <?php echo htmlspecialchars($row['descripcion'], ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                    <div class="form-group" style="margin-bottom: 10px;">
                                        <label for="quantity-<?php echo $row['id']; ?>" style="font-size: 14px;">Cantidad:</label>
                                        <div style="display: flex; align-items: center; justify-content: center; gap: 5px;">
                                            <button type="button" onclick="decreaseQuantity('quantity-<?php echo $row['id']; ?>')" class="quantity-btn" <?php echo ($row['cantidad'] == 0) ? 'disabled' : ''; ?>>-</button>
                                            <input type="number" id="quantity-<?php echo $row['id']; ?>" name="quantity" value="1" min="1" max="<?php echo (int)$row['cantidad']; ?>" readonly class="quantity-input" <?php echo ($row['cantidad'] == 0) ? 'disabled' : ''; ?>>
                                            <button type="button" onclick="increaseQuantity('quantity-<?php echo $row['id']; ?>')" class="quantity-btn" <?php echo ($row['cantidad'] == 0) ? 'disabled' : ''; ?>>+</button>
                                        </div>
                                    </div>
                                    <a href="javascript:void(0);" onclick="<?php echo ($row['cantidad'] == 0) ? 'noStockAlert(); return false;' : "addToCart({$row['id']}, document.getElementById('quantity-{$row['id']}').value, {$row['cantidad']})"; ?>" class="btn btn-primary btn-sm" style="margin-top: 10px; width: 100%;">
                                        <i class="fa fa-shopping-cart"></i> Agregar al carrito
                                    </a>
                                </article>
                            </div>
                    <?php }
                    } else {
                        
                        echo '<div class="col-12 text-center py-5"><h4>No hay muebles disponibles</h4></div>';
                    }
                    ?>
                </div>
            </div>
        </section>


        <?php pg_close($conn); ?>

        <!-- Footer -->
        <footer class="section novi-background footer-advanced bg-gray-700">
            <div class="footer-advanced-main">
                <div class="container">
                    <div class="row row-50">
                        <div class="col-lg-4">
                            <h5 class="font-weight-bold text-uppercase text-white">Sobre nosotros</h5>
                            <p class="footer-advanced-text">HomeSafe es una tienda online donde se pueden comprar inmuebles y muebles. Este sitio web tiene una interfaz fácil de usar e intuitiva.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-advanced-aside">
                <div class="container">
                    <div class="footer-advanced-layout">
                        <div>
                            <ul class="list-nav">
                                <li><a href="index.php">Inicio</a></li>
                                <li><a href="about-us.php">Sobre nosotros</a></li>
                                <li><a href="properties.php">Propiedades</a></li>
                                <li><a href="furniture.php">Muebles</a></li>
                            </ul>
                        </div>
                        <div>
                            <ul class="foter-social-links list-inline list-inline-md">
                                <li><a class="icon novi-icon icon-sm link-default mdi mdi-instagram" href="https://www.instagram.com/homesafe25"></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container">
                <hr>
            </div>
            <div class="footer-advanced-aside">
                <div class="container">
                    <div class="footer-advanced-layout">
                        <a class="brand" href="index.php">
                            <img src="images/logo-light-115x34.png" alt="" width="115" height="34" srcset="images/logo-light-115x34.png 2x" />
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Global Mailform Output-->
    <div class="snackbars" id="form-output-global"></div>

    <!-- Javascript-->
    <script src="js/core.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        
        let searchTimeout;
        let isSearching = false;
        let currentFurnitureId = null;

        
        function increaseQuantity(inputId) {
            const input = document.getElementById(inputId);
            if (!input || input.disabled) return;
            const max = parseInt(input.max);
            let currentValue = parseInt(input.value);
            if (currentValue < max) {
                input.value = currentValue + 1;
            }
        }

        
        function decreaseQuantity(inputId) {
            const input = document.getElementById(inputId);
            if (!input || input.disabled) return;
            const min = parseInt(input.min);
            let currentValue = parseInt(input.value);
            if (currentValue > min) {
                input.value = currentValue - 1;
            }
        }

        
        function addToCart(id, quantity, availableQuantity) {
            quantity = parseInt(quantity);
            availableQuantity = parseInt(availableQuantity);
            if (quantity <= 0) {
                noStockAlert();
                return;
            }
            if (quantity > availableQuantity) {
                Swal.fire("No puedes agregar más unidades de las disponibles.", "", "warning");
                return;
            }

            fetch('addcarrito.php?id=' + id + '&quantity=' + quantity, {
                    method: 'GET',
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire(data.message, "", "success");
                    } else {
                        Swal.fire(data.message || "Error al agregar al carrito", "", "error");
                    }
                })
                .catch(() => {
                    Swal.fire("Error de conexión", "", "error");
                });
        }
        
        function noStockAlert() {
            Swal.fire("No puedes agregar este producto al carrito porque ya no quedan unidades disponibles.", "", "warning");
        }

        function generateAverageStars(rating, totalReviews) {
            const roundedRating = Math.round(rating * 2) / 2; 
            let starsHtml = '<div class="average-rating-container" style="display: flex; align-items: center; justify-content: center; gap: 5px; margin: 8px 0;">';

            for (let i = 1; i <= 5; i++) {
                if (i <= roundedRating) {
                    starsHtml += '<i class="fa fa-star" style="color: #FFC107; font-size: 14px;"></i>';
                } else if (i - 0.5 === roundedRating) {
                    starsHtml += '<i class="fa fa-star-half-o" style="color: #FFC107; font-size: 14px;"></i>';
                } else {
                    starsHtml += '<i class="fa fa-star-o" style="color: #ddd; font-size: 14px;"></i>';
                }
            }

            starsHtml += `<span style="font-size: 12px; color: #666; margin-left: 4px;">(${rating.toFixed(1)}) ${totalReviews} reseña${totalReviews !== 1 ? 's' : ''}</span>`;
            starsHtml += '</div>';

            return starsHtml;
        }

        
        function searchFurniture() {
            const query = document.getElementById('searchInput').value.trim();
            const resultsContainer = document.getElementById('searchResults');

            
            clearTimeout(searchTimeout);

            if (query.length === 0) {
                resultsContainer.classList.remove('show');
                resultsContainer.innerHTML = '';
                return;
            }

            if (query.length < 2) {
                return; 
            }

            
            resultsContainer.innerHTML = '<div class="search-loading">Buscando...</div>';
            resultsContainer.classList.add('show');
            isSearching = true;

            
            searchTimeout = setTimeout(() => {
                performSearchRequest(query);
            }, 300);
        }

        
        function performSearchRequest(query) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'search_furniture.php?q=' + encodeURIComponent(query), true);

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    isSearching = false;
                    const resultsContainer = document.getElementById('searchResults');

                    if (xhr.status === 200) {
                        try {
                            const results = JSON.parse(xhr.responseText);
                            displaySearchResults(results);
                        } catch (e) {
                            console.error('Error parsing JSON:', e);
                            resultsContainer.innerHTML = '<div class="no-results">Error en la búsqueda</div>';
                        }
                    } else {
                        resultsContainer.innerHTML = '<div class="no-results">Error de conexión</div>';
                    }
                }
            };

            xhr.send();
        }

        
        function displaySearchResults(results) {
            const resultsContainer = document.getElementById('searchResults');
            resultsContainer.innerHTML = '';

            if (results.length > 0) {
                results.forEach(function(result) {
                    const resultItem = document.createElement('div');
                    resultItem.className = 'search-result-item';
                    resultItem.innerHTML = `
                        <i class="fa fa-cube search-result-icon"></i>
                        <span>${result.nombre}</span>
                    `;

                    resultItem.onclick = function() {
                        loadFurnitureDetails(result.id);
                        $('#detailsModal').modal('show');
                        resultsContainer.classList.remove('show');
                        document.getElementById('searchInput').value = result.nombre;
                    };

                    resultsContainer.appendChild(resultItem);
                });
            } else {
                resultsContainer.innerHTML = '<div class="no-results"><i class="fa fa-search"></i> No se encontraron muebles</div>';
            }

            resultsContainer.classList.add('show');
        }

        
        function performSearch() {
            const query = document.getElementById('searchInput').value.trim();
            if (query.length > 0) {
                if (!isSearching) {
                    performSearchRequest(query);
                }
            }
        }

        
        document.addEventListener('click', function(event) {
            const searchContainer = document.querySelector('.search-container');
            const resultsContainer = document.getElementById('searchResults');

            if (!searchContainer.contains(event.target)) {
                resultsContainer.classList.remove('show');
            }
        });

        
        document.getElementById('searchInput').addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                performSearch();
            }
        });

        
        function getStarHtml(rating) {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                if (i <= rating) {
                    stars += '<i class="fa fa-star" style="color: #FFC107;"></i>';
                } else {
                    stars += '<i class="fa fa-star-o" style="color: #ccc;"></i>';
                }
            }
            return stars;
        }

        function loadFurnitureDetails(furnitureId) {
            currentFurnitureId = furnitureId;
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'furnituredetalles.php?id=' + furnitureId, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    var details = response.furniture;
                    var reviews = response.reviews;
                    document.getElementById('furnitureName').textContent = details.nombre;
                    if (details.avg_rating && details.total_reviews > 0) {
                        const avgRatingHtml = generateAverageStars(parseFloat(details.avg_rating), parseInt(details.total_reviews));
                        const nameElement = document.getElementById('furnitureName');
                        nameElement.innerHTML = details.nombre + '<br><div style="margin-top: 10px;">' + avgRatingHtml + '</div>';
                    } else {
                        document.getElementById('furnitureName').innerHTML = details.nombre + '<br><div style="margin-top: 10px; font-size: 12px; color: #999; font-style: italic;">Sin reseñas aún</div>';
                    }

                    
                    if (details.descuento > 0) {
                        document.getElementById('furniturePrice').innerHTML =
                            `<span style="font-weight:bold; color:#dc3545;">$${details.precio}</span>
                            <span style="text-decoration: line-through; color: #888; margin-left: 8px;">$${details.precio_original}</span>
                            <div class="discount-percentage-badge">- ${details.descuento}%</div>`;
                    } else {
                        document.getElementById('furniturePrice').textContent = `$${details.precio}`;
                    }

                    document.getElementById('furnitureQuantity').textContent = details.cantidad;
                    document.getElementById('furnitureDescription').textContent = details.descripcion;
                    document.getElementById('furnitureImage').src = "data:image/jpeg;base64," + details.imagen;
                    document.getElementById('furnitureId').value = details.id;

                    var quantityInput = document.getElementById('quantity-modal');
                    var decrementButton = document.getElementById('btn-decrement-modal');
                    var incrementButton = document.getElementById('btn-increment-modal');
                    quantityInput.max = details.cantidad;
                    quantityInput.value = 1;

                    if (details.cantidad > 0) {
                        decrementButton.disabled = false;
                        incrementButton.disabled = false;
                    } else {
                        decrementButton.disabled = true;
                        incrementButton.disabled = true;
                    }

                    
                    const reviewsContainer = document.getElementById('furnitureReviewsContainer');
                    reviewsContainer.innerHTML = '';
                    const noReviewsMessage = document.getElementById('noReviewsMessage');

                    if (reviews.length > 0) {
                        noReviewsMessage.style.display = 'none';
                        reviews.forEach(function(review) {
                            const reviewDiv = document.createElement('div');
                            reviewDiv.className = 'review-item mb-3 p-3 border rounded';
                            reviewDiv.style.borderColor = '#ddd';
                            reviewDiv.style.backgroundColor = '#fefefe';

                            const starsHtml = getStarHtml(review.rating);
                            const reviewDate = new Date(review.created_at).toLocaleDateString('es-ES', {
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            });

                            
                            const isCurrentUser = <?php echo isset($_SESSION['email']) ? "'" . $_SESSION['email'] . "'" : 'null'; ?> === review.user_email;
                            const actionButtons = isCurrentUser ? `
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-primary me-2" onclick="openEditReviewModal(${review.id}, '${details.nombre}', ${review.rating}, '${review.comment.replace(/'/g, "\\'")}')">
                                        <i class="fa fa-edit"></i> Editar
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteReview(${review.id})">
                                        <i class="fa fa-trash"></i> Eliminar
                                    </button>
                                </div>
                            ` : '';

                            reviewDiv.innerHTML = `
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="font-weight-bold">${htmlspecialchars(review.user_email)}</div>
                                    <div class="star-display">${starsHtml}</div>
                                </div>
                                <p class="mb-1">${htmlspecialchars(review.comment)}</p>
                                <small class="text-muted">Publicado el: ${reviewDate}</small>
                                ${actionButtons}
                            `;
                            reviewsContainer.appendChild(reviewDiv);
                        });
                    } else {
                        noReviewsMessage.style.display = 'block';
                    }
                }
            };
            xhr.send();
        }

        function getCurrentFurnitureId() {
            return currentFurnitureId;
        }

        function addToCartFromModal() {
            var furnitureId = document.getElementById('furnitureId').value;
            var quantity = document.getElementById('quantity-modal').value;
            var availableQuantity = document.getElementById('furnitureQuantity').textContent;

            if (quantity <= 0) {
                noStockAlert();
                return;
            }
            if (parseInt(quantity) > parseInt(availableQuantity)) {
                Swal.fire("No puedes agregar más unidades de las disponibles.", "", "warning");
                return;
            }

            addToCart(furnitureId, quantity, availableQuantity);
        }

        function toggleFavorite(muebleId, buttonElement) {
            const heartIcon = buttonElement.querySelector('i');
            const isFavorited = heartIcon.classList.contains('fa-heart');

            fetch('0_toggle_favorite.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        mueble_id: muebleId,
                        action: isFavorited ? 'remove' : 'add'
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        if (isFavorited) {
                            heartIcon.className = 'fa fa-heart-o';
                            heartIcon.style.color = 'rgb(221, 90, 76)';
                        } else {
                            heartIcon.className = 'fa fa-heart';
                            heartIcon.style.color = 'rgb(231, 76, 60)';
                        }
                    } else {
                        alert(result.message || 'Error al procesar favorito');
                    }
                })
                .catch(error => {
                    alert('Error de conexión');
                });
        }

        
        function openReviewModal(muebleId, furnitureName) {
            <?php if (!isset($_SESSION['email'])) : ?>
                Swal.fire('Debes iniciar sesión para añadir una reseña.', '', 'warning');
                return;
            <?php endif; ?>
            document.getElementById('reviewFurnitureId').value = muebleId;
            document.getElementById('reviewFurnitureName').textContent = furnitureName;
            document.getElementById('reviewForm').reset();
            updateCharCounter(document.getElementById('reviewComment'), 'charCounter');
            $('#reviewModal').modal('show');
        }

        function updateCharCounter(textarea, counterId) {
            const currentLength = textarea.value.length;
            const maxLength = textarea.maxLength;
            document.getElementById(counterId).textContent = `${currentLength}/${maxLength}`;
        }

        function submitReview() {
            const muebleId = document.getElementById('reviewFurnitureId').value;
            const rating = document.querySelector('input[name="rating"]:checked')?.value;
            const comment = document.getElementById('reviewComment').value;

            if (!rating) {
                Swal.fire('Por favor, selecciona una calificación de estrellas.', '', 'warning');
                return;
            }
            if (comment.trim() === '') {
                Swal.fire('Por favor, escribe un mensaje para tu reseña.', '', 'warning');
                return;
            }

            fetch('submit_review.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        mueble_id: muebleId,
                        rating: rating,
                        comment: comment
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        Swal.fire('¡Reseña enviada con éxito!', '', 'success');
                        $('#reviewModal').modal('hide');
                    } else {
                        Swal.fire('Error al enviar la reseña: ' + (result.message || 'Inténtalo de nuevo.'), '', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error de conexión al enviar la reseña.', '', 'error');
                });
        }
        
        function openEditReviewModal(reviewId, furnitureName, currentRating, currentComment) {
            
            document.getElementById('reviewModalLabel').innerHTML = 'Editar Reseña para <span id="reviewFurnitureName">' + furnitureName + '</span>';

            
            document.getElementById('reviewFurnitureId').value = reviewId; 
            document.getElementById('reviewFurnitureName').textContent = furnitureName;

            
            document.querySelector(`input[name="rating"][value="${currentRating}"]`).checked = true;

            
            document.getElementById('reviewComment').value = currentComment;
            updateCharCounter(document.getElementById('reviewComment'), 'charCounter');

            
            const submitBtn = document.querySelector('#reviewModal .btn-primary');
            submitBtn.textContent = 'Actualizar Reseña';
            submitBtn.onclick = function() {
                updateReview(reviewId);
            };

            
            $('#reviewModal').modal('show');
        }

        
        function updateReview(reviewId) {
            const rating = document.querySelector('input[name="rating"]:checked')?.value;
            const comment = document.getElementById('reviewComment').value;

            if (!rating) {
                Swal.fire('Por favor, selecciona una calificación de estrellas.', '', 'warning');
                return;
            }
            if (comment.trim() === '') {
                Swal.fire('Por favor, escribe un mensaje para tu reseña.', '', 'warning');
                return;
            }

            fetch('edit_review.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        review_id: reviewId,
                        rating: rating,
                        comment: comment
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        Swal.fire('¡Reseña actualizada con éxito!', '', 'success');
                        $('#reviewModal').modal('hide');
                        
                        const furnitureId = getCurrentFurnitureId(); 
                        if (furnitureId) {
                            loadFurnitureDetails(furnitureId);
                        }
                    } else {
                        Swal.fire('Error al actualizar la reseña: ' + (result.message || 'Inténtalo de nuevo.'), '', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error de conexión al actualizar la reseña.', '', 'error');
                });
        }

        
        function deleteReview(reviewId) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esta acción",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('delete_review.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                review_id: reviewId
                            })
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                Swal.fire('¡Reseña eliminada!', 'Tu reseña ha sido eliminada.', 'success');
                                
                                const furnitureId = getCurrentFurnitureId();
                                if (furnitureId) {
                                    loadFurnitureDetails(furnitureId);
                                }
                            } else {
                                Swal.fire('Error al eliminar la reseña: ' + (result.message || 'Inténtalo de nuevo.'), '', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error de conexión al eliminar la reseña.', '', 'error');
                        });
                }
            });
        }

        
        function htmlspecialchars(str) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return str.replace(/[&<>"']/g, function(m) {
                return map[m];
            });
        }
        
        $('#reviewModal').on('hidden.bs.modal', function() {
            
            document.getElementById('reviewModalLabel').innerHTML = 'Añadir Reseña para <span id="reviewFurnitureName"></span>';

            
            const submitBtn = document.querySelector('#reviewModal .btn-primary');
            submitBtn.textContent = 'Enviar Reseña';
            submitBtn.onclick = function() {
                submitReview();
            };

            
            document.getElementById('reviewForm').reset();
            updateCharCounter(document.getElementById('reviewComment'), 'charCounter');
        });
    </script>

    <script src="js/validacionesES.js"></script>
</body>

</html>