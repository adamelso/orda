<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Download;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Yaml\Yaml;

class LoadAllTheThings implements FixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $data = $this->getData();

        foreach ($data['downloads'] as $entry) {
            $file = $this->container->getParameter('kernel.root_dir').'/Resources/data/media/'.$entry['file'];

            $d = new Download();

            $d->setName($entry['name']);
            $d->setFilePath($file);

            $manager->persist($d);
        }
        $manager->flush();
    }

    /**
     * @return array
     */
    private function getData()
    {
        $config = <<<YAML
orda:
  downloads:
    - name: "Nytrix ft. DEV - Electric Walk [Ookay Remix]"
      file: "nytrix--ft-dev--electric-walk--ookay-remix.mp3"
    - name: "Martin Garrex - Proxy"
      file: "martin-garrix--proxy.mp3"
    - name: "BoB - Ray Bands [CRNKN Remix]"
      file: "bob--ray-bands--crnkn-remix.mp3"
YAML;


        return Yaml::parse($config)['orda'];
    }
}
