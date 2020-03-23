<?php
// The following hack is necessary to get Twig templates rendered
// Hacks start
require_once('../../vendor/autoload.php');
require_once('../../src/TopNav.php');
use Symfony\Component\Translation\Translator;
global $twig;
$loader = new \Twig\Loader\FilesystemLoader('../../templates/');
$twig = new \Twig\Environment($loader, [
    // 'cache' => $_SERVER["DOCUMENT_ROOT"] . '/../var/cache/lsp',
]);
$tr = new Translator("en");
$nav = new App\TopNav($tr);
$mock_app = ["request" => ["pathinfo" => $_SERVER["REQUEST_URI"], "query" => $_GET]];
$twig->addGlobal('navbar', $nav);
$twig->addGlobal('app', $mock_app);
$twig->addExtension(new Twig_Extensions_Extension_I18n());

function twig_render(string $template, array $params) {
    global $twig;
    // Determine a successful login
    $auth_failure = false;
    switch (GET('action')) {
        case 'logout' : logout(); break;
        case 'login' : 
            if (!login()) {
                $auth_failure = true;
            }
            break;
    }
    $common = [
        'category_list' => get_categories(),
        'username' => SESSION(),
        'is_admin' => is_admin(get_user_id(SESSION())),
        'auth_failure' => $auth_failure,
        'commentsearch' => GET('commentsearch', false) ? 'checked' : '',
        'sort' => GET('sort', 'date'),
        'category' => GET('category'),
        'subcategory' => GET('subcategory')
    ];
    $merged_params = array_merge($params, $common);
    return $twig->render($template, $merged_params);
}
// Hacks end
