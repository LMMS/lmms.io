#!/bin/env python3
import os
import sys
import subprocess
import webbrowser
import time
import threading
import shutil
import tempfile
import json
from urllib import request


class LMMSIOGettext(object):

    def __init__(self, debug=False, output=None):
        self.debug = debug
        self.runner = self.headless_run
        self.php_srv = None
        self.output = output
        self.php_files = []
        self.php_pages_path = os.path.join(
            os.path.dirname(__file__), '../public/')
        self.tmpnam = None
        if self.debug:
            self.runner = self.headful_run
        return

    def headless_run(self, fn):
        req = request.urlopen(url='http://localhost:8000/{}'.format(fn))
        print(req.read().decode('utf-8').replace('<br>', '\n'))
        return

    def headful_run(self, fn):
        webbrowser.open(url='http://localhost:8000/{}'.format(fn))
        time.sleep(10)
        return

    def run_php_srv(self):
        self.php_srv = subprocess.Popen(['php', '-S', 'localhost:8000', '-t',
                                         os.path.join(os.path.dirname(__file__),
                                                      '../public')])
        return

    def gen_cache(self):
        if os.path.exists('/tmp/cache/twig/lmms.io'):
            shutil.rmtree('/tmp/cache/twig/lmms.io')
        if not php_extract_script:
            raise Exception('This script is corrupted!!!')
        twig_tpls_path = os.path.join(
            os.path.dirname(__file__), '../templates/')
        twig_files = ''
        for subdirs, dirs, files in os.walk(twig_tpls_path):
            for f in files:
                item = os.path.join(os.path.relpath(
                    start=twig_tpls_path, path=subdirs), f)
                twig_files += ('%r,' % item)
        twig_files = twig_files[:-1]
        if self.debug:
            print('Found:', twig_files)
        with tempfile.NamedTemporaryFile(dir=self.php_pages_path, suffix='.php') as f:
            cont = php_extract_script.replace('__FILES__', twig_files)
            f.write(cont.encode('utf-8'))
            f.flush()
            wrk = self.run_php_srv
            srv = threading.Thread(target=wrk, args=())
            srv.start()
            time.sleep(2.0)
            self.runner(os.path.basename(f.name))
            self.php_srv.kill()
            return

    def invoke_gettext(self):
        for dirs, _, files in os.walk('/tmp/cache/twig/lmms.io/'):
            for f in files:
                self.php_files.append(os.path.join(dirs, f))
        cmd = ['xgettext', '-L', 'php', '--from-code=utf-8',
               '-o', self.output] + self.php_files
        subprocess.check_call(cmd)

    def convert_filename(self, name):
        with tempfile.NamedTemporaryFile(dir=self.php_pages_path, mode='w+t', suffix='.php') as src:
            src.write(php_convert_script.replace(
                '__CC__', '\x00').replace('__FILE__', name))
            src.flush()
            output = subprocess.check_output(['php', src.name])
        return output.split(b'\x00')

    def convert_filenames(self):
        with open(self.output, 'rt') as fl:
            content = fl.read()
        for f in self.php_files:
            fn, lns = self.convert_filename(f)  # Filename line-numbers
            lns = json.loads(lns.decode('utf-8'))
            fn = fn.decode('utf-8').strip()
            content = content.replace(f, fn)
            while len(lns) > 0:
                val_pair = lns.popitem()
                content = content.replace('%s:%s' % (
                    fn, val_pair[0]), '%s:%s' % (fn, val_pair[1]))
        with open(self.output, 'w+t') as fl:
            fl.write(content)

    def run(self):
        self.gen_cache()
        self.invoke_gettext()
        self.convert_filenames()
        if os.path.exists('/tmp/cache/twig/lmms.io'):
            shutil.rmtree('/tmp/cache/twig/lmms.io')


def main():
    debug = False
    flag = False
    output = '/tmp/lmms.io.pot'
    if len(sys.argv) > 1:
        for i in sys.argv:
            if i == '--webbrowser':
                debug = True
            elif i == '-o':
                flag = True
                continue
            elif flag:
                flag = False
                output = i
    LMMSIOGettext(debug, output).run()


php_extract_script = """
<?php
require_once('app.php');
use Symfony\Component\HttpFoundation\Response;
$tmpdir = '/tmp/cache/twig/lmms.io/';
$tplDir = '../templates/';
$app['twig']->setCache($tmpdir);
echo 'Initiating a dry-run...<br>';
echo 'Temp files are stored in ' . $tmpdir . '<br>';
require_once('../views.php');
$files = array(__FILES__);
foreach ($files as $file)
{
        echo 'Loading ' . $file . '...<br>';
        try {
          $app['twig']->loadTemplate($file);
        } catch (Exception $e) {
          echo '<br>ERROR:<br>' . $e->getMessage();
          break;
        }
}
die();
?>
"""

php_convert_script = """
<?php
require_once(__DIR__ . '/../vendor/autoload.php');
$targetFile = '__FILE__';
$classNamePat = '/class (\w+) extends Twig_Template/';
$fileContent = file_get_contents($targetFile);
if ($fileContent === FALSE) {
  die();
}
preg_match($classNamePat, $fileContent, $className);
$loader = new Twig_Loader_Filesystem(__DIR__ . '/../templates');
$twig = new Twig_Environment($loader, array(
    'auto_reload' => false
));
$v0=null;
require_once($targetFile);
eval('$v0=new ' . $className[1] . '($twig);');
echo $v0->getTemplateName() . '__CC__' . json_encode($v0->getDebugInfo());
?>
"""

if __name__ == '__main__':
    main()
