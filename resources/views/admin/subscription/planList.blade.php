@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box ">
    <section class="project-doorbox">
       <div class="ai-training-data-wrapper d-flex align-items-baseline justify-content-between">
         <div class="heading-content-box">
            <h2>Alle plannen</h2>
            <div id="successMessage" class="alert alert-success d-none"></div>
            @if (session('success'))
                <div class="alert alert-success" role="alert" id="success-message">
                    {{ session('success') }}
                </div>
            @endif


            
            <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry...</p>
        </div>

        <a href="{{ route('dashboard.admin.add-plans') }}" class="btn btn-green">Nieuwe plannen toevoegen</a>

       </div> 
       <div id="notificationMessage" class="alert d-none" role="alert"></div>
        <div class="project-ongoing-box">
           <table class="table table-striped table-bordered table-notification-list">
                <thead>
                    <tr>
                        <th>Naam</th>
                        <th>Ideal for</th>
                        <th>Description</th>
                        <th>Prijs</th>
                        <th>Plantype</th>
                        <th>Feestype</th>
                        <th>Modaltype</th>
                        <th>Actie</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $currencySymbols = [
                            'USD' => '$',
                            'EUR' => '€',
                            'INR' => '₹',
                            'GBP' => '£',
                            'JPY' => '¥',
                            // Add more if needed
                        ];
                    @endphp
                    @forelse($plans as $plan)
                        <tr>
                            <td>{{ $plan->name}}</td>
                            <td>{{ $plan->ideal_for}}</td>
                            <td>
                                <div class="scrollable-td">
                                     @php
                                        $descriptions = json_decode($plan->description, true);

                                        // Clean each description by removing \r and \n
                                        if (is_array($descriptions)) {
                                            $cleanDescriptions = array_map(function($desc) {
                                                return preg_replace('/\r|\n/', '', $desc);
                                            }, $descriptions);

                                            echo implode(', ', $cleanDescriptions);
                                        } else {
                                            echo $plan->description;
                                        }
                                    @endphp
                                 </div>
                            </td>
                            
                            <td> {{ $currencySymbols[$plan->currency] ?? $plan->currency }} {{ number_format($plan->price, 2) }}</td>
                            <td>{{ $plan->plan_type }}</td> 
                            <td>{{ $plan->fees_type == 1 ? 'Gratis' : ($plan->fees_type == 2 ? 'Betaald' : 'Unknown') }}</td>
                            <td>{{ $plan->modal_type == 1 ? 'Gewicht winnen' : ($plan->modal_type == 2 ? 'Gewicht verliezen' : 'Unknown') }}</td>
                            <td>                                            
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('dashboard.admin.edit-plan', ['id' => $plan->id]) }}" class="action-btn">
                                    <i class="fas fa-edit"></i> <span class="edit-span"></span>
                                    </a>
                                    <button  class="dropdown-item delete-btn-design delete-plan-btn d-flex justify-content-center" data-user-id="{{ $plan->id }}" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                                        <i class="fa fa-regular fa-trash "></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                    <tr>
                      <td colspan="12" class="text-center">Geen gegevens gevonden.</td>
                    </tr>
                  @endforelse                 
                </tbody>
            </table>
        </div>
        @if ($plans->lastPage() > 1)
            <nav class="pt-3" aria-label="Page navigation">
                <ul class="pagination" id="pagination-links">
                    {{-- Previous Page --}}
                    @if ($plans->onFirstPage())
                        <li class="page-item disabled"><span class="page-link text-dark">Vorige</span></li>
                    @else
                        <li class="page-item"><a class="page-link text-dark" href="{{ $plans->previousPageUrl() }}">Vorige</a></li>
                    @endif

                    {{-- Page Numbers --}}
                    @for ($i = 1; $i <= $plans->lastPage(); $i++)
                        <li class="page-item {{ $plans->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $plans->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    {{-- Next Page --}}
                    @if ($plans->hasMorePages())
                        <li class="page-item"><a class="page-link text-dark" href="{{ $plans->nextPageUrl() }}">Volgende</a></li>
                    @else
                        <li class="page-item disabled"><span class="page-link text-dark">Volgende</span></li>
                    @endif
                </ul>
            </nav>
        @endif


    </section>  
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- delete-confirmation-popup -->
    <!-- <section class="delete-confirmation-popup delete-plan-confirmation-popup">
        <div class="popup__content">
            <div class="close cancel-popup-btnbox">
                <i class="fas fa-times"></i>
            </div>
            <div class="asign-popup-content">
            <div class="delete-confirmation-popup-body">
            
                <h2 class="delete-confirmation-popup-title delete-user-confirmation-popup-title">Weet je het zeker?</h2>
                <p class="delete-confirmation-popup-text delete-user-confirmation-popup-text">Wil je dit project echt verwijderen?</p>
                <div class="delete-confirmation-popup-footer delete-user-confirmation-popup-footer">
                    <button class="delete-confirmation-popup-btn delete-confirmation-popup-cancel-btn delete-user-confirmation-popup-cancel-btn cancel-popup-btnbox">Annuleren</button>
                    <button class="delete-confirmation-popup-btn delete-confirmation-popup-delete-btn delete-plan-confirmation-popup-delete-btn" data-user-id="">Wis</button>
                </div>
            
            </div>
            </div>
        </div>
        
    </section> -->


    <!-- Modal -->
        <section class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h2 class="delete-confirmation-popup-title delete-user-confirmation-popup-title" id="staticBackdropLabel">Weet je het zeker?</h2>
                        <button type="button" class="btn-close cancel-popup-btnbox" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body delete-confirmation-popup-body ">
                            <p class="delete-confirmation-popup-text delete-user-confirmation-popup-text">Wil je dit project echt verwijderen?</p>
                    </div>
                    <div class="modal-footer border-0 delete-confirmation-popup-footer delete-user-confirmation-popup-footer">
                        <button class="delete-confirmation-popup-btn btn" data-bs-dismiss="modal" aria-label="Close">Annuleren</button>
                        <button class="delete-confirmation-popup-btn btn delete-confirmation-popup-delete-btn delete-plan-confirmation-popup-delete-btn" data-user-id="">Wis</button>
                    </div>
                </div>
            </div>
        </section>
    <!-- delete-confirmation-popup-->
@endsection

<script>
    const csrfToken = "{{ csrf_token() }}";
     const deletePlanUrl = "{{ url('dashboard/admin/delete-plan') }}"; 
</script>
