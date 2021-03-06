<?php

namespace App\Controller;
use App\Entity\Coach;
use App\Entity\SimpleUtilisateur;
use App\Entity\Tournoi;
use App\Entity\Utilisateur;
use App\Form\CoachType;
use App\Form\SimpleUtilisateurType;
use App\Repository\CoachRepository;
use App\Repository\EquipeRepository;
use App\Repository\SimpleUtilisateurRepository;
use App\Repository\UtilisateurRepository;
use App\Security\EmailVerifier;
use App\Security\UsersAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\DocBlock\Serializer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Twilio\Serialize;

class SimpleutilisateurController extends AbstractController
{


    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/profile", name="profile")
     */
    public function index(): Response
    {
        $user = $this->getUser();
        $tournois=$this->getDoctrine()->getRepository(Tournoi::class)->findAll();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('simpleutilisateur/profile.html.twig', [
            'user' => $user,'tournois'=>$tournois,
        ]);
    }

    /**
     * @Route("/affichesimpleutilisateur", name="affichesimpleutilisateur"  , methods={"GET"})
     */
    public function readsimpleutilisateur(SimpleUtilisateurRepository $repository, Request $request)
    {if (null != $request->get('searchname')) {
        $evenement = $this->getDoctrine()->getRepository(SimpleUtilisateur::class)->findBy(['username' => $request->get('searchname')]);

        return $this->render('/simpleutilisateur/list.html.twig', [
            'l' => $evenement,'user'=>$this->getUser()
        ]);
    }
        $evenement = $repository->findAll();

        return $this->render('/simpleutilisateur/list.html.twig', [
            'l' => $evenement,'user'=>$this->getUser()
        ]);
    }

    /**
     * @Route("/activitesimpleutilisateur/{id}", name="activitesimpleutilisateur")
     */
    public function activitesimpleutilisateur($id, UtilisateurRepository $repository)
    {
        $simpleutilisateur = $repository->find($id);
        $em = $this->getDoctrine()->getManager();
        $simpleutilisateur->setIsVerified(true);
        $em->persist($simpleutilisateur);
        $em->flush();
        return $this->redirectToRoute("afficheactive");
    }

    /**
     * @Route("/desactiveutilisateur/{id}", name="desactiveutilisateur")
     */
    public function desactiveutilisateur($id, UtilisateurRepository $repository)
    {
        $simpleutilisateur = $repository->find($id);
        $em = $this->getDoctrine()->getManager();
        $simpleutilisateur->setIsVerified(false);
        $em->persist($simpleutilisateur);
        $em->flush();
        return $this->redirectToRoute("app_login");
    }

    /**
     * @Route("/desactivesimpleutilisateur/{id}", name="desactivesimpleutilisateur")
     */
    public function desactivesimpleutilisateur($id, UtilisateurRepository $repository)
    {
        $simpleutilisateur = $repository->find($id);
        $em = $this->getDoctrine()->getManager();
        $simpleutilisateur->setIsVerified(false);
        $em->persist($simpleutilisateur);
        $em->flush();
        return $this->redirectToRoute("affichedesactive");
    }

    /**
     * @Route("/deletesimpleutilisateur/{id}", name="deletesimpleutilisateur")
     */
    public function deletesimpleutilisateur($id, SimpleUtilisateurRepository $repository)
    {
        $simpleutilisateur = $repository->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($simpleutilisateur);
        $em->flush();
        return $this->redirectToRoute("profile");
    }

    /**
     * @Route("/addUtilisateur", name="addUtilisateur")
     */

    public function addUtilisateur(Request $request, UserPasswordEncoderInterface $userPasswordEncoder, GuardAuthenticatorHandler $guardHandler, UsersAuthenticator $authenticator, EntityManagerInterface $entityManager)
    { $tournois=$this->getDoctrine()->getRepository(Tournoi::class)->findAll();
        $Utilisateur = new SimpleUtilisateur();
        $form = $this->createForm(SimpleUtilisateurType::class, $Utilisateur);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form["verifpassword"]->getData() != $form["password"]->getData()) {
                return $this->render('simpleutilisateur/add.html.twig', [
                    "form" => $form->createView(),
                    "error" => "Les mots de passe ne correspondent pas!",
                    "user" => $Utilisateur
                ]);
            } else {
                //$Utilisateur->setPassword(MD5($Utilisateur->getPassword(), PASSWORD_DEFAULT));
                 $Utilisateur->setVerifPassword(password_hash($Utilisateur->getVerifPassword(), PASSWORD_DEFAULT));
                $Utilisateur->setRoles(['ROLE_USER']);
                $Utilisateur->setIsVerified(true);
                $em = $this->getDoctrine()->getManager();
                $em->persist($Utilisateur);
                $em->flush();


                return $this->redirectToRoute("app_login",["user" => $Utilisateur,"tournois" => $tournois,]);
            }
        }
        return $this->render("simpleutilisateur/add.html.twig", ['form' => $form->createView(), "user" => $Utilisateur, 'error' => '',"tournois" => $tournois,]);
    }

    /**
     * @Route("/updateUtilisateur/{id}", name="updateUtilisateur")
     */

    public function updateUtilisateur($id, Request $request, UtilisateurRepository $repository)
    {$tournois=$this->getDoctrine()->getRepository(Tournoi::class)->findAll();
        $simpleutilisateur = $repository->find($id);
        $form = $this->createForm(SimpleUtilisateurType::class, $simpleutilisateur);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($simpleutilisateur);
            $em->flush();
            return $this->redirectToRoute('profile');
        }
        return $this->render("simpleutilisateur/modif.html.twig", ['form' => $form->createView(), 'user' => $simpleutilisateur, 'error' => '','tournois'=>$tournois]);
    }

    /**
     * @Route("/simpleutilisateur/tri", name="tri")
     */
    public function Tri(Request $request)
    {
        $em = $this->getDoctrine()->getManager();


        $query = $em->createQuery(
            'SELECT a FROM App\Entity\SimpleUtilisateur a 
            ORDER BY a.username'
        );

        $activites = $query->getResult();
        return $this->render('simpleutilisateur/list.html.twig',
            array('l' => $activites,'user'=>$this->getUser()));

    }

    /**
     * @Route("/demandecoach/{id}", name="demandecoach")
     */
    public function demandecoach(Request $request, $id, UtilisateurRepository $utilisateurRepository)
    {  $tournois=$this->getDoctrine()->getRepository(Tournoi::class)->findAll();
        $user = $utilisateurRepository->find($id);
        $user->setRole(1);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute("profile",['tournois'=>$tournois]);
    }

    /**
     * @Route("/listedesdemandes", name="listedesdemandes")
     */
    public function listedesdemandes()
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT a FROM App\Entity\SimpleUtilisateur a 
            where a.role = 1'
        );

        $u = $query->getResult();
        return $this->render('simpleutilisateur/listdesdemandes.html.twig',
            array('l' => $u,'user'=>$this->getUser()));
    }

    /**
     * @Route("/afficheactive", name="afficheactive")
     */
    public function afficheactive()
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT a FROM App\Entity\Utilisateur a 
            where a.isVerified = 1'
        );

        $u = $query->getResult();
        return $this->render('simpleutilisateur/listdactivie.html.twig',
            array('l' => $u,'user'=>$this->getUser()));
    }

    /**
     * @Route("/affichedesactive", name="affichedesactive")
     */
    public function affichedesactive()
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery(
            'SELECT a FROM App\Entity\Utilisateur a 
            where a.isVerified = 0'
        );

        $u = $query->getResult();
        return $this->render('simpleutilisateur/listdesactive.html.twig',
            array('l' => $u,'user'=>$this->getUser()));
    }

    /**
     * @Route("/devenircoach/{id}", name="devenircoach")
     */

    public function devenircoach($id, SimpleUtilisateurRepository $utilisateurRepository)
    {
        $user = $utilisateurRepository->find($id);
        $this->container->get('security.token_storage')->setToken(null);
        $coach = new Coach();
        $coach->setRole(0);
        $coach->setIsVerified(true);

        $coach->setUserName($user->getUserName());
        $coach->setVerifPassword($user->getVerifPassword());
        $coach->setEmail($user->getEmail());
        $coach->setPassword($user->getPassword());

        $coach->setRoles(['ROLE_COACH']);
        $coach->setSpecialite("rien");
        $em = $this->getDoctrine()->getManager();

        $em->persist($coach);
        $em->remove($user);
        $em->flush();


        return $this->redirectToRoute('affichecoach');

    }

    /**
     * @Route("/annulerlademande/{id}", name="annulerlademande")
     */
    public function annulerlademande($id, SimpleUtilisateurRepository $utilisateurRepository)
    {
        $user = $utilisateurRepository->find($id);
        $user->setRole(0);
        $em = $this->getDoctrine()->getManager();

        $em->persist($user);

        $em->flush();


        return $this->redirectToRoute('affichecoach');

    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser(),);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_login');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_login');
    }

    /**
     * @Route("desactivationcompteparmail", name="desactivationcompteparmail")
     */
    public function desactivationcompteparmail(Request $request): Response
    {$Utilisateur=$this->getUser();
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $Utilisateur,
            (new TemplatedEmail())
                ->from(new Address('Gclaim.Gclaim@esprit.tn', 'G_Claim'))
                ->to($Utilisateur->getEmail())
                ->subject(' Confirmer la d??sactivation par mail ')
                ->htmlTemplate('simpleutilisateur/confirmation_email.html.twig')
            ->context(['user'=>$Utilisateur])
        );
        return $this->redirectToRoute('profile');
    }
    /**
     * @Route("/addusermobile", name="addusermobile")
     */
    public function addusermobile(Request $request,NormalizerInterface $normalizer)
    {$em=$this->getDoctrine()->getManager();
        $user=new SimpleUtilisateur();
        $user->setUserName($request->get('username'));
        $user->setPassword($request->get('password'));
        $user->setVerifPassword($request->get('verifpassword'));
        $user->setEmail($request->get('email'));
        $user->setFullName($request->get('fullname'));
        $user->setRole(0);
        $user->setIsVerified(true);
        $user->setRoles(['ROLE_USER']);
        $em->persist($user);
        $em->flush();
        $jsonContent=$normalizer->normalize($user,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));

    }
    /**
     * @Route("/signinmobile", name="signinmobile")
     */
    public function signinmobile(Request $request,NormalizerInterface $normalizer)
    {
        $username=$request->query->get('username');
        $password=$request->query->get('password');
        $em=$this->getDoctrine()->getManager();
        $user=$em->getRepository(Utilisateur::class)->findOneBy(['username'=>$username]);

       if($user)
       {if($user->getPassword()==$password)
       {
           $jsonContent=$normalizer->normalize($user,'json',['groups'=>'post:read']);
           return new Response(json_encode($jsonContent));
       }
       else {
           return new Response("failed");
       }
       }
       else {
           return new Response("failed");
       }


    }
    /**
     * @Route("/updateusermobile/{id}", name="updateusermobile")
     */
    public function updateusermobile($id,Request $request,NormalizerInterface $normalizer)
    {$em=$this->getDoctrine()->getManager();
        $user=$em->getRepository(Utilisateur::class)->find($id);
        $user->setUserName($request->get('username'));
        $user->setPassword($request->get('password'));
        $user->setVerifPassword($request->get('verifpassword'));
        $user->setEmail($request->get('email'));
        $user->setFullName($request->get('fullname'));
        $em->persist($user);
        $em->flush();
        $jsonContent=$normalizer->normalize($user,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));

    }
    /**
     * @Route("/listUsers", name="listUsers")
     */
    public function listUsers(SimpleUtilisateurRepository $repository,Request $request,NormalizerInterface $normalizable)
    {
        $equipe=$repository->findall();
        $jsonContent =$normalizable->normalize($equipe,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }
    /**
     * @Route("/listCoach", name="listCoach")
     */
    public function listCoach(CoachRepository  $repository,Request $request,NormalizerInterface $normalizable)
    {
        $equipe=$repository->findall();
        $jsonContent =$normalizable->normalize($equipe,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @Route("/deleteUser/{id}", name="deleteUser")
     */
    public function deleteUser($id,UtilisateurRepository  $repository,Request $request,NormalizerInterface $normalizable)
    {$em=$this->getDoctrine()->getManager();
        $equipe=$repository->find($id);
        $em->remove($equipe);
        $em->flush();
        $jsonContent =$normalizable->normalize($equipe,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));
    }
    /**
     * @Route("/addcoachmobile", name="addcoachmobile")
     */
    public function addcoachmobile(Request $request,NormalizerInterface $normalizer)
    {$em=$this->getDoctrine()->getManager();
        $user=new Coach();
        $user->setUserName($request->get('username'));
        $user->setPassword($request->get('password'));
        $user->setVerifPassword($request->get('verifpassword'));
        $user->setEmail($request->get('email'));
        $user->setSpecialite($request->get('specialite'));
        $user->setRole(0);
        $user->setRoles(['ROLE_COACH']);
        $user->setIsVerified(1);
        $em->persist($user);
        $em->flush();
        $jsonContent=$normalizer->normalize($user,'json',['groups'=>'post:read']);
        return new Response(json_encode($jsonContent));

    }
}
