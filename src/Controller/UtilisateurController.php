<?php

namespace App\Controller;


use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Repository\UtilisateurRepository;
use App\Form\AjoutUtilisateurType;
use App\Form\ModifPdpType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\HttpFoundation\File\File;


class UtilisateurController extends AbstractController
{
    /**
     * @Route("/ajouter_utilisateur", name="ajouter_utilisateur")
     */
    public function ajoutUtilisateur(Request $request)
    {
        $utilisateur = new Utilisateur();
       
        $form = $this->createForm(AjoutUtilisateurType::class,$utilisateur);        
        
        if ($request->isMethod('POST')){            
          $form -> handleRequest ($request);            
          if($form->isValid()){    
            $utilisateur->setDateinscription(new \DateTime());        
            $em = $this->getDoctrine()->getManager();              
            $em->persist($utilisateur);              
            $em->flush();   
          
         
         
          } 
          return $this->redirectToRoute('ajouter_utilisateur');
        }
        
     
        return $this->render('ajouter_utilisateur/ajouter_utilisateur.html.twig', [
          'form'=>$form->createView()
        ]);
    }



    /**
     * @Route("/liste_utilisateurs", name="liste_utilisateurs")
     */
    public function listeUtilisateurs(Request $request)
    {
      $em = $this->getDoctrine();
      $repoUtilisateur = $em-> getRepository(Utilisateur::class);
      $utilisateurs = $repoUtilisateur->findBy(array(),array('nom'=>'ASC'));
    return $this->render('liste_utilisateurs/liste_utilisateurs.html.twig', [
    'utilisateurs'=>$utilisateurs
  ]);
}

    /**
     * @Route("/profil_utilisateur/{id}", name="profil_utilisateur", requirements={"id"="\d+"})
     */
    public function profilUtilisateur(int $id,Request $request)
    {
      $em = $this->getDoctrine();
      $repoUtilisateur = $em-> getRepository(Utilisateur::class);
      $utilisateur = $repoUtilisateur->find($id);
      $form = $this->createForm(ModifPdpType::class,$utilisateur);
      if($utilisateur->getPdp()== NULL){
        $path = $this->getParameter('picture_directory').'/defaut.jpeg';
      }else{
        $path = $this->getParameter('picture_directory').'/'.$utilisateur->getPdp();
      }

      $f = new File($path);
      $data = file_get_contents($path);
      $base64 = 'data:image/'.$f->guessExtension().';base64,'.base64_encode($data);

      if ($request->isMethod('POST')) {            
          $form->handleRequest($request);
          if ($form->isSubmitted() && $form->isValid()) {
              $file = $form->get('pdpfile')->getData();

              $em = $this->getDoctrine()->getManager();
             
              $fileName = $utilisateur->getId().'.'.$file->guessExtension(); 
              $utilisateur->setpdp($fileName);
                $em->persist($utilisateur);
              $em->flush(); 
              try{    
                  $file->move($this->getParameter('picture_directory'),$fileName); // Nous déplaçons lefichier dans le répertoire configuré dans services.yaml
                  $this->addFlash('notice', 'Fichier inséré');

              } catch (FileException $e) {                // erreur durant l’upload            }
                  $this->addFlash('notice', 'Problème fichier inséré');
              }
          return $this->redirectToRoute('profil_utilisateur',['id'=>$utilisateur->getId()]);
      }        
  }

      return $this->render('profil_utilisateur/profil_utilisateur.html.twig', [
        'utilisateur'=>$utilisateur,
         'form'=>$form->createView(),
         'base64'=>$base64

      ]);
}






}
