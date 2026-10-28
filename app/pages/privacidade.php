<?php
render('privacidade', ['titulo' => 'Privacidade', 'voltar' => url(usuario_atual() ? 'conta' : '/'), 'semAbas' => !usuario_atual()]);
