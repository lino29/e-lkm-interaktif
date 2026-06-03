@props(['steps' => [], 'currentStep' => 0])

<div class="flex gap-2 flex-wrap my-3.5">
    @foreach($steps as $index => $step)
        @php
            $isDone = $index < $currentStep;
            $isActive = $index == $currentStep;
            $isLocked = $index > $currentStep;
            
            $classes = "px-3 py-2.5 rounded-full border font-extrabold text-[13px] ";
            if ($isDone) {
                $classes .= "bg-[#e4f8ef] text-elkm-primary-2 border-[#c7eadb]";
            } elseif ($isActive) {
                $classes .= "bg-elkm-primary text-white border-elkm-primary";
            } else {
                $classes .= "bg-[#f1f4f3] text-[#a0aaa6] border-elkm-line";
            }
        @endphp
        <span class="{{ $classes }}">
            {{ $step }}
        </span>
    @endforeach
</div>
