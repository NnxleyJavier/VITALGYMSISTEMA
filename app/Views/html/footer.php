</div> <style>
                .footer-vital {
                    background-color: #ffffff; /* Footer blanco para contrastar con el fondo gris */
                    color: #555;
                    padding: 20px 40px;
                    border-top: 1px solid #e0e0e0;
                    display: flex;
                    justify-content: space-between;
                }
                .footer-vital p { margin: 0; font-size: 14px; }
                .footer-vital a { color: #c4b50d; font-weight: bold; text-decoration: none; }
                .footer-vital a:hover { text-decoration: underline; color: #a19307; }
            </style>

            <footer class="footer-vital">
                <p>&copy; <?= date('Y') ?> <strong>Vital GYM Fitness</strong>. Todos los derechos reservados.</p>
                <p><a href="<?= base_url('/soporte') ?>"><span class="glyphicon glyphicon-headphones"></span> Soporte Técnico</a></p>
            </footer>

        </div> </div> <script src="<?= base_url('assets/lib/jquery.min.js')?>"></script> 
    <script src="<?= base_url('assets/lib/bootstrap.min.js')?>"></script> 
    
</body>
</html>