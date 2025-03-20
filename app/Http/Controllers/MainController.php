<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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

    dd($quiz);
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
}
