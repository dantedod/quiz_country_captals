<?php

namespace App\Http\Controllers;

use App\View\Components\Answer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

use function Laravel\Prompts\alert;

class MainController extends Controller
{
  private $app_data;

  public function __construct()
  { //Carregar app_data.php file de outro app folder
    $this->app_data = require(app_path('app_data.php'));
  }

  public function startGame(): View
  {
    return view('home');
  }

  public function prepareGame(Request $request)
  {
    $request->validate(
      [
        'total_questions' => 'required|min:3|max:30|integer'
      ],
      [

        'total_questions.required' => "numero de questoes sao obrigatorios",
        'total_questions.min' => "O numero minimo de questoes eh 3",
        'total_questions.max' => "O numero maximo de questoes sao 30",
        'total_questions.integer' => "O valor passado para o numero de questoes tem q ser um inteiro",

      ]
    );
    //agr pegar total de questoes
    $total_questions = intval($request->input('total_questions'));

    //preparar a estrutura do quiz
    $quiz = $this->prepareQuiz($total_questions);

    //colocar o quis na sessao
    session()->put([
      'quiz' => $quiz,
      'total_questions' => $total_questions,
      'current_questions' => 1,
      'correct_answers' => 0,
      'wrong_answers' => 0
    ]);

    return redirect()->route('game');
  }

  private function prepareQuiz($total_questions)
  {
    $questions = [];

    $total_contries = count($this->app_data);

    //criar index de countries para so ter questoes unicas
    $indexes = range(0, $total_contries - 1);
    shuffle($indexes);
    $indexes = array_slice($indexes, 0, $total_questions);
    $question_number = 1;
    foreach ($indexes as $index) {

      $question['question_number'] = $question_number++;
      $question['country'] = $this->app_data[$index]['country'];
      $question['correct_answer'] = $this->app_data[$index]['capital'];

      //respostas erradas
      $otherCapitals = array_column($this->app_data, 'capital');

      //tira resposta correta
      $otherCapitals = array_diff($otherCapitals, [$question['correct_answer']]);

      //shuffle a resposta errada
      shuffle($otherCapitals);
      $question['wrong_answers'] = array_slice($otherCapitals, 0, 3);

      //perceber se a  resposta da pergunta eh correta ou nao
      $question['correct'] = null;

      $questions[] = $question;
    }
    return $questions;
  }

  public function game(): View
  {
    $quiz = session('quiz');
    $total_questions = session('total_questions');
    $current_question = session('current_questions') - 1;

    //preparar as perguntas para mostrar na view
    $answers = $quiz[$current_question]['wrong_answers'];
    $answers[] = $quiz[$current_question]['correct_answer'];

    shuffle($answers);

    return view('game')->with([
      'country' => $quiz[$current_question]['country'],
      'totalQuestions' => $total_questions,
      'currentQuestion' => $current_question,
      'answers' => $answers

    ]);
  }

  public function answer($enc_answer)
  {
    try {
      $answer = Crypt::decryptString($enc_answer);
    } catch (\Throwable $e) {
      return redirect()->route('game');
    }

    // Lógica do game
    $quiz = session('quiz');
    $current_question = session('current_questions') - 1;
    $correct_answer = $quiz[$current_question]['correct_answer'];
    $correct_answers = session('correct_answers');
    $wrong_answers = session('wrong_answers');

    // Verificar se a resposta está correta
    if ($answer == $correct_answer) {
      $correct_answers++;  // Incrementa o contador de respostas corretas
      $quiz[$current_question]['correct'] = true;
    } else {
      $wrong_answers++;  // Incrementa o contador de respostas erradas
      $quiz[$current_question]['correct'] = false;
    }

    // Atualizar sessão
    session()->put([
      'quiz' => $quiz,
      'correct_answers' => $correct_answers,
      'wrong_answers' => $wrong_answers
    ]);

    // Preparar os dados para mostrar a resposta correta
    $data = [
      'country' => $quiz[$current_question]['country'],
      'correct_answer' => $correct_answer,
      'chouce_answer' => $answer,
      'current_question' => $current_question,
      'total_questions' => session('total_questions')
    ];

    return view('answer_result')->with($data);
  }


  public function nextQuestion()
  {
    $current_question = session('current_questions');
    $total_questions = session('total_questions');

    // Checar se o jogo acabou
    if ($current_question < $total_questions) {
      $current_question++;
      session()->put('current_questions', $current_question);
      return redirect()->route('game');
    } else {
      // Game over
      return redirect()->route('showResults');
    }
  }

  public function showResults()
  {
    echo "mostrar resultados finais";
    dd(session()->all());
  }
}
