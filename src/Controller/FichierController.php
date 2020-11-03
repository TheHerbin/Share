<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\AjoutFichierType;
use App\Entity\Fichier;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\FichierRepository;
use Symfony\Component\HttpFoundation\File\File;




class FichierController extends AbstractController
{
    /**
     * @Route("/ajout_fichier", name="ajout_fichier")
     */
    public function ajoutFichier(Request $request)
    {
        $fichier = new Fichier();
        $form = $this->createForm(AjoutFichierType::class,$fichier);

        if ($request->isMethod('POST')) {            
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $file = $fichier->getNom();
                $em = $this->getDoctrine()->getManager();
                $fichier->setVNom($file->getClientOriginalName());
                $fichier->setDate(new \DateTime()); //récupère la date du jour
                $fichier->setExtension($file->guessExtension()); // Récupère l’extension du fichier
                $fichier->setTaille($file->getSize()); // getSize contient la taille du fichier envoyé
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                $fichier->setNom($fileName);
                $em->persist($fichier);
                $em->flush();    
               
                try{    
                    $file->move($this->getParameter('file_directory'),$fileName); // Nous déplaçons lefichier dans le répertoire configuré dans services.yaml
                    $this->addFlash('notice', 'Fichier inséré');

                } catch (FileException $e) {                // erreur durant l’upload            }
                    $this->addFlash('notice', 'Problème fichier inséré');
                }
            return $this->redirectToRoute('ajout_fichier');
        }        
    }

        return $this->render('ajout_fichier/ajout_fichier.html.twig', [
           'form'=>$form->createView()
        ]);
    }
    /**     
     * * @return string     
     * 
     * */    
    private function generateUniqueFileName()    
    {        
        return md5(uniqid());    
    }





      /**
     * @Route("/liste_fichiers", name="liste_fichiers")
     */
    public function listeFichiers(Request $request)
    {
      $em = $this->getDoctrine();
      $repoFichier = $em-> getRepository(Fichier::class);
      $fichiers = $repoFichier->findBy(array(),array('vNom'=>'ASC'));
      if ($request->get('supp')!=null){
        $fichier = $repoFichier->find($request->get('supp'));
        if($fichier!=null){
            $em->getManager()->remove($fichier);
            $em->getManager()->flush();
        }
        return $this->redirectToRoute('liste_fichiers');
      }
    return $this->render('liste_fichiers/liste_fichiers.html.twig', ['fichiers'=>$fichiers

  ]);
  
}



/**
     * @Route("/telechargement_fichier/{id}", name="telechargement_fichier", requirements={"id"="\d+"})
     */
    public function telechargementFichier(int $id)
    {
      $em = $this->getDoctrine();
      $repoFichier = $em->getRepository(Fichier::class);  
      
      $fichier = $repoFichier->find($id);
      $file = $fichier->getNom();
      if ($fichier == null){
        $this->redirectToRoute('liste_fichiers');
      }
      else{
        $fuchier = new File($this->getParameter('file_directory').'/'.$fichier->getNom());
        
        return $this->file($fuchier, $fichier->getvNom());  
      
      }
    }

}