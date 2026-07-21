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

#[Route('/dashboard')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController {
    public function index(Request $request, StudentRepository $studentRepository): Response {
        $totalStudents = $studentRepository->count([]);
        $classrooms = $studentRepository->findDistinctClassrooms();
        $recentStudents =$studentRepository->findBy([], ['createdAt' => 'DESC'], 5);
        $studentByClass = [];

        foreach ($classrooms as $classroom) {
            $studentByClass[$classroom] = $studentRepository->count(['classroom' => $classroom]);
        }

        return $this->render('dashboard/index.html.twig', [
            'totalStudents' => $totalStudents,
            'classrooms' => $classrooms,
            'recentStudents' => $recentStudents,
            'studentsByClass' => $studentByClass,
        ]);
    }

    #[Route('/students', name: 'app_dashboard_students')]
    public function students(Request $request, StudentRepository $studentRepository): Response {
        $searchTerm = $request->query->get('search');
        $classroom = $request->query->get('classroom');
        $students = $studentRepository->searchStudents($searchTerm, $classroom);
        $classrooms = $studentRepository->findDistinctClassrooms();

        return $this->render('dashboard/students.html.twig', [
            'students' => $students,
            'searchTerm' => $searchTerm,
            'classrooms' => $classrooms,
            'selectedClassroom' => $classroom,
        ]);
    }

    #[Route('/student/new', name: 'app_dashboard_student_new', methods: ['GET', 'POST'])]
    public function newStudent(Request $request, EntityManagerInterface $entityManager): Response {
        $student = new Student();
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($student);
            $entityManager->flush();

            $this->addFlash('success', 'Etudiant ajouter avec succès');
            return $this->redirectToRoute('app_dashboard_students');
        }

        return $this->render('dashboard/student_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter un étudiant',
        ]);
    }

    #[Route('/student/{id}/edit', name: 'app_dashboard_student_edit', methods: ['GET', 'POST'])]
    public function editStudent(Request $request, Student $student, EntityManagerInterface $entityManager): Response {
        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $student->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Etudiant modifié avec succès');
            return $this->redirectToRoute('app_dashboard_students');
        }

        return $this->render('dashboard/student_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier un étudiant',
            'student' => $student,
        ]);
    }

    #[Route('/student/{id}/delete', name: 'app_dashboard_delete', methods: ['POST'])]
    public function deleteStudent(Request $request, Student $student, EntityManagerInterface $entityManager): Response {
        if ($this->isCsrfTokenValid('delete' . $student->getId(), $request->request->get('_token'))) {
            $entityManager->remove($student);
            $entityManager->flush();
            $this->addFlash('success', 'Etudiant supprimé avec succès');
        }

        return $this->redirectToRoute('app_dashboard_students');
    }


}