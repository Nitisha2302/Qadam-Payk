@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box ">
    <section class="project-doorbox">
        <div class="heading-content-box heading-text-center-main">
            <h2 class="text-center">Plan maken</h2>
            <div id="successMessage" class="alert alert-success d-none"></div>

            @if (session('success'))
                <div class="alert alert-success" role="alert" id="success-message">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger" role="alert" id="error-message">
                    {{ session('error') }}
                </div>
            @endif

            <p style="text-align: center !important;">Lorem Ipsum is simply dummy text of the printing and typesetting industry...</p>
        </div>

        <div class="project-ongoing-box">
            <form class="employe-form" action="{{route('dashboard.admin.store-plans')}}" method="POST"  enctype="multipart/form-data">
                  @csrf
                  <div class="form-container">
                                    <div class="row">
                    <div class="col-md-12 step-field" id="step-1">
                        <div class="form-group mb-4">
                            <label for="meal_name">Plan Naam</label>
                            <input type="text" id="plan_name" name="plan_name" class="form-control" placeholder="Plan Naam" value="{{ old('plan_name') }}">
                            @error('plan_name')
                                <div class="text-danger error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>


                    <div class="col-md-12 step-field d-none" id="step-2">
                        <div class="form-group mb-4">
                            <label for="fees_type">Fees Type</label>
                            <select id="fees_type" name="fees_type" class="form-control">
                                <option value="">Selecteer FeesType</option>
                                <option value="Free" {{ old('fees_type') == 'Free' ? 'selected' : '' }}>Gratis</option>
                                <option value="Paid" {{ old('fees_type') == 'Paid' ? 'selected' : '' }}>Betaald</option>
                            </select>
                            @error('fees_type')
                                <div class="text-danger error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    
                   <div class="col-md-12 step-field d-none" id="step-3">
                        <div class="form-group mb-4">
                            <label for="modal_type">Modal Type</label>
                            <select id="modal_type" name="modal_type" class="form-control">
                                <option value="">Selecteer Modal</option>
                               
                                <option value="weight_gain" {{ old('modal_type') == 'weight_gain' ? 'selected' : '' }}>Gewicht winnen</option>
                                 <option value="weight_loss" {{ old('modal_type') == 'weight_loss' ? 'selected' : '' }}>Gewicht verliezen</option>
                            </select>
                            @error('modal_type')
                                <div class="text-danger error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    
                    <div class="col-md-12 step-field d-none" id="price-field">
                        <div class="form-group mb-4">
                            <label for="price">Prijs(€)</label>
                            <input type="text" id="price" name="price" class="form-control" placeholder="Prijs" value="{{ old('price') }}">
                            @error('price')
                                <div class="text-danger error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12 step-field d-none" id="step-5">
                        <div class="form-group mb-4">
                            <label for="plan_type">Plantype</label>
                            <select id="plan_type" name="plan_type" class="form-control">
                                <option value="">Selecteer Plantype</option>
                                <option value="Wekelijks" {{ old('plan_type') == 'Wekelijks' ? 'selected' : '' }}>Wekelijks</option>
                                <option value="Maandelijks" {{ old('plan_type') == 'Maandelijks' ? 'selected' : '' }}>Maandelijks</option>
                                <option value="Jaarlijks" {{ old('plan_type') == 'Jaarlijks' ? 'selected' : '' }}>Jaarlijks</option>
                            </select>
                            @error('plan_type')
                                <div class="text-danger error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    

                    <div class="col-md-12 step-field d-none" id="step-6">
                        <div class="form-group mb-4">
                            <label for="description">Kenmerken</label>
                            <textarea id="description" style="height:49px;" name="description" class="form-control user-input"  placeholder="Kenmerken van het plan">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="text-danger error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12 step-field d-none" id="step-7">
                        <div class="form-group mb-4">
                            <label for="ideal_for">Ideal For</label>
                            <input type="text" id="ideal_for" name="ideal_for" class="form-control" placeholder="Ideal for" value="{{ old('ideal_for') }}">
                            @error('ideal_for')
                                <div class="text-danger error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                     
                    <div class="col-md-12">
                        <button type="submit" class="btn-box btn-submt-user py-block justify-content-center ms-0 mt-3">
                            Plan maken
                        </button>
                    </div>
                </div>
                  </div>
            </form>
        </div>
    </section>  
</div>



@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    $(document).ready(function () {

        function showSteps() {
            let name = $('#plan_name').val().trim();
            let feesType = $('#fees_type').val();
            let modalType = $('#modal_type').val();
            let price = $('#price').val().trim();
            let planType = $('#plan_type').val();
            let idealFor = $('#ideal_for').val();
                       // For description, use CKEditor data, not textarea value
            let description = '';

            if (CKEDITOR_INSTANCE) {
                description = CKEDITOR_INSTANCE.getData().trim();
            } else {
                description = $('#description').val().trim();
            }

            // Step 1: Plan name filled → Show fees type
            if (name !== '') {
                $('#step-2').removeClass('d-none');
            } else {
                $('#step-2, #step-3, #price-field, #step-5, #step-6,#step-7').addClass('d-none');
                return; // Stop if first step is empty
            }

            // Step 2: Fees type filled → Show modal type
            if (feesType !== '') {
                $('#step-3').removeClass('d-none');
            } else {
                $('#step-3, #price-field, #step-5, #step-7,#step-6').addClass('d-none');
                return; // Stop if fees type is empty
            }

            // Step 3: If Paid → Show price field, else hide price field
            if (feesType === 'Paid') {
                $('#price-field').removeClass('d-none');

                // Price is mandatory for Paid to show next step
                if (price !== '') {
                    $('#step-5').removeClass('d-none');
                } else {
                    $('#step-5, #step-7').addClass('d-none');
                    return; // Stop if price is empty
                }

            } else {
                // Free: Skip price, go directly to next step if modal is selected
                $('#price-field').addClass('d-none');
                if (modalType !== '') {
                    $('#step-5').removeClass('d-none');
                } else {
                    $('#step-5, #step-7,#step-6').addClass('d-none');
                    return; // Stop if modal type is empty
                }
            }

            // Step 4: Show Description if Plan Type is selected
            if (planType !== '') {
                $('#step-6').removeClass('d-none');
            } else {
                $('#step-6').addClass('d-none');
            }

            // Step 5: Show ideal for if description is selected
            if (description  !== '') {
                $('#step-7').removeClass('d-none');
            } else {
                $('#step-7').addClass('d-none');
            }
        }
                // Initialize CKEditor instance variable
        let CKEDITOR_INSTANCE;

        ClassicEditor
            .create(document.querySelector('#description'))
            .then(editor => {
                CKEDITOR_INSTANCE = editor;

                // When CKEditor content changes, trigger showSteps to update visibility
                editor.model.document.on('change:data', () => {
                    showSteps();
                });
            })
            .catch(error => {
                console.error(error);
            });

        // Initial check (for validation errors)
        showSteps();

        // Listen to input changes
        $('#plan_name, #fees_type, #modal_type, #price, #plan_type,#description').on('input change', function () {
            showSteps();
        });
    });
</script> -->

<!-- CKEditor 4 -->
<!-- <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script> -->

<script>
    $(document).ready(function () {
        let CKEDITOR_INSTANCE;

        // Initialize CKEditor 4
        CKEDITOR_INSTANCE = CKEDITOR.replace('description', {
            allowedContent: true,
            height: 300, // or 1000 if needed
            on: {
                instanceReady: function (evt) {
                    evt.editor.focus();
                    const editorBody = evt.editor.document.getBody();
                    editorBody.setStyle('padding', '55px');
                    editorBody.setStyle('background-color', '#ffffff');
                },
                change: function () {
                    showSteps(); // Trigger steps check on content change
                }
            }
        });

        function showSteps() {
            let name = $('#plan_name').val().trim();
            let feesType = $('#fees_type').val();
            let modalType = $('#modal_type').val();
            let price = $('#price').val().trim();
            let planType = $('#plan_type').val();
            let idealFor = $('#ideal_for').val();

            //  Use CKEditor 4 API to get content
            let description = CKEDITOR_INSTANCE.getData().trim();

            if (name !== '') {
                $('#step-2').removeClass('d-none');
            } else {
                $('#step-2, #step-3, #price-field, #step-5, #step-6,#step-7').addClass('d-none');
                return;
            }

            if (feesType !== '') {
                $('#step-3').removeClass('d-none');
            } else {
                $('#step-3, #price-field, #step-5, #step-7,#step-6').addClass('d-none');
                return;
            }

            if (feesType === 'Paid') {
                $('#price-field').removeClass('d-none');

                if (price !== '') {
                    $('#step-5').removeClass('d-none');
                } else {
                    $('#step-5, #step-7').addClass('d-none');
                    return;
                }
            } else {
                $('#price-field').addClass('d-none');
                if (modalType !== '') {
                    $('#step-5').removeClass('d-none');
                } else {
                    $('#step-5, #step-7,#step-6').addClass('d-none');
                    return;
                }
            }

            if (planType !== '') {
                $('#step-6').removeClass('d-none');
            } else {
                $('#step-6').addClass('d-none');
            }

            if (description !== '') {
                $('#step-7').removeClass('d-none');
            } else {
                $('#step-7').addClass('d-none');
            }
        }

        // Trigger visibility checks on input/select changes
        $('#plan_name, #fees_type, #modal_type, #price, #plan_type').on('input change', function () {
            showSteps();
        });

        // Run once on load
        setTimeout(() => showSteps(), 500); // Delay ensures CKEditor is ready
    });
</script>






