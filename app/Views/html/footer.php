<style>
        .footer-vital {
            background-color: #ffffff;
            color: #7e8299;
            padding: 25px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 16px;
            margin: 0 30px 30px 30px;
            box-shadow: 0 10px 30px 0 rgba(82, 63, 105, 0.03);
            font-family: 'Poppins', sans-serif;
        }
        .footer-vital a { color: #c4b50d; font-weight: 600; text-decoration: none; transition: color 0.3s; }
        .footer-vital a:hover { color: #a19307; text-decoration: none; }
    </style>

    <footer class="footer-vital">
        <p class="mb-0">&copy; <?= date('Y') ?> <strong>Vital GYM Fitness</strong>. Todos los derechos reservados.</p>
        <p class="mb-0">
            <a href="<?= base_url('/soporte') ?>">
                <span class="glyphicon glyphicon-headphones" style="margin-right: 5px;"></span> Soporte Técnico
            </a>
        </p>
    </footer>

    </div> </div> 

</body>
</html>