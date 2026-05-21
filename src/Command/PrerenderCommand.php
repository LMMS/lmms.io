<?php
namespace App\Command;

use App\Controller\DownloadController;
use App\Controller\NewsController;

use LMMS\Artifacts;
use LMMS\GraphQl;
use LMMS\Releases;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Twig\Environment;

#[AsCommand(name: 'app:prerender')]
class PrerenderCommand
{
	public function __construct(
		private Artifacts $artifacts,
		private GraphQl $graphql,
		private ParameterBagInterface $parameterBag,
		private Releases $releases,
		private Environment $twig
	)
	{
	}

	public function __invoke(): int
	{
		$this->write('/', $this->twig->render('home.twig'));
		$this->write('/download/', $this->twig->render(
			'download/index.twig',
			(new DownloadController())->getVars($this->releases, $this->artifacts)
		));
		$this->write('/news/', $this->twig->render('news/index.twig', [
			'news' => (new NewsController())->getHtml($this->graphql)
		]));
		$this->write('/get-involved/', $this->twig->render('get-involved.twig'));
		$this->write('/showcase/', $this->twig->render('showcase.twig'));
		$this->write('/competitions/', $this->twig->render('competitions.twig'));
		$this->write('/branding/', $this->twig->render('branding.twig'));
		$this->write('/wiki/', $this->twig->render('wiki.twig'));

		return Command::SUCCESS;
	}

	private function write($path, $html) {
		$path = $this->parameterBag->get('kernel.project_dir') . '/public' . $path;
		if (!is_dir($path)) {
			mkdir($path, recursive: true);
		}
		file_put_contents($path . '/index.html', $html);
	}
}
