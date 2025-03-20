<x-main-layout pageTitle="Countires and Capitals Quiz">

    <div class="container">
        <x-question :country="$country" :currentQuestion="$current_question" :totalQuestions="$total_questions" />


        <div class="text-center fs-3 mb-3">
            Resposta correta: <span class="text-info">{{ $correct_answer }}</span>
        </div>

        <div class="text-center fs-3 mb-3">
            A sua resposta: <span class="[conditional]">{{ $chouce_answer }}</span>
        </div>

    </div>

    <!-- cancel game -->
    <div class="text-center mt-5">
        <a href="{{ route('nextQuestion') }}" class="btn btn-primary mt-3 px-5">AVANÇAR</a>
    </div>

</x-main-layout>
