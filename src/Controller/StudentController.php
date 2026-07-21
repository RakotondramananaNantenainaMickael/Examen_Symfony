<?php
namespace App\Controller;

use App\Entity\Student;
use App\Form\StudentType;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/students')]
class StudentController extends AbstractController {
    #[Route('/', name: 'app_students_index', method: ['GET'])]
    public function index(Request $request, StudentRepository $studentRepository): Response {
        $searchTerm = $request->querry->get('search');
        $classroom = $request->querry->get('classroom');

        $students = $studentRepository->searchStudents($searchTerm, $classroom);
        $classrooms = $studentRepository->findDistinctClassrooms();

        return $this->render('student/index.html.twig', [
            'students' => $students,
            'searchTerm' => $searchTerm,
            'classrooms' => $classrooms,
            'selectedClassroom' => $classroom,
        ]);
    }

    #[Route('/new', name:'app_student_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response {
        $student = new Student();
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($student);
            $entityManager->flush();

            $this->addFlash('success', 'Etudiant ajouter avec succès');
            return $this->redirectToRoute('app_students_index');
        }

        return $this->render('student/new.html.twig', [
            'form' => $form->CreateView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_student_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, Student $student, EntityManagerInterface $entityManager): Response {
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $student->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Etudiant modifié avec succes');
            return $this->redirectToRoute('app_students_index');
        }

        return $this->render('student/edit.html.twig', [
            'form' => $form->createView(),
            'student' => $student,
        ]);
    }

    #[Route('/{id}', name: 'app_student_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Student $student, EntityManagerInterface $entityManager): Response {
        if ($this->isCsrfTokenValid('delete', $student->getId(), $request->request->get('_token'))){
            $entityManager->remove($student);
            $entityManager->flush();
            $this->addFlash('success', 'Etudiant supprimé avec succès');
        }

        return $this->redirectToRoute('app_students_index');
    }

    
}