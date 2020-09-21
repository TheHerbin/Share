<?php

namespace App\Controller;


use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Theme;
use App\Form\AjoutThemeType;
use App\Repository\ThemeRepository;


class ThemeController extends AbstractController
{
    /**
     * @Route("/theme", name="ajout_theme")
     */
    public function ajoutTheme(Request $request)
    {
        $theme = new Theme();
       
        $form = $this->createForm(AjoutThemeType::class,$theme);        
        
        if ($request->isMethod('POST')){            
          $form -> handleRequest ($request);            
          if($form->isValid()){              
            $em = $this->getDoctrine()->getManager();              
            $em->persist($theme);              
            $em->flush();        
          $this->addFlash('notice','Thème ajouté'); 
         
          } 
          return $this->redirectToRoute('ajout_theme');
        }
        
     
        return $this->render('theme/ajout_theme.html.twig', [
          'form'=>$form->createView()
        ]);
    }


    /**
     * @Route("/liste_themes", name="liste_themes")
     */
    public function listeThemes(Request $request)
    {
      $em = $this->getDoctrine();
      $repoTheme = $em-> getRepository(Theme::class);
      $themes = $repoTheme->findBy(array(),array('libelle'=>'ASC'));
    return $this->render('liste_themes/liste_themes.html.twig', [
    'themes'=>$themes
  ]);
}


}
