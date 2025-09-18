@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box ">
    <section class="project-doorbox">
        <div class="heading-content-box heading-text-center-main">
            <h2 class="text-center">Plan bijwerken</h2>
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

            <p style="text-align:center;">Lorem Ipsum is simply dummy text of the printing and typesetting industry...</p>
        </div>

        <div class="project-ongoing-box">
            <form class="employe-form" action="{{ route('dashboard.admin.updatePlans', ['id' => $plans->id]) }}" method="POST"  enctype="multipart/form-data">
                  @csrf
                  <div class="form-container">
                    <div class="row">
                    <div class="col-md-12">
                        <div class="form-group mb-4">
                            <label for="plan_name">Plan Naam</label>
                            <input type="text" id="plan_name" name="plan_name" class="form-control" placeholder="Plan Naam" value="{{ old('plan_name', $plans->name ?? '') }}">
                            @error('plan_name')
                                <div class="text-danger error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                

                    <div class="col-md-12">
                        <div class="form-group mb-4">
                            <label for="fees_type">Maaltijd Naam</label>
                            <select id="fees_type" name="fees_type" class="form-control">
                                <option value="">Selecteer Maaltijd</option>
                                <option value="Free" {{ old('fees_type', $plans->fees_type == 1 ? 'Free' : 'Paid') == 'Free' ? 'selected' : '' }}>Gratis</option>
                                <option value="Paid" {{ old('fees_type', $plans->fees_type == 1 ? 'Free' : 'Paid') == 'Paid' ? 'selected' : '' }}>Betaald</option>
                            </select>
                            @error('fees_type')
                                <div class="text-danger error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mb-4">
                            <label for="modal_type">Modal Type</label>
                            <select id="modal_type" name="modal_type" class="form-control">
                                <option value="">Selecteer Modal</option>
                                <option value="1" {{ old('modal_type', $plans->modal_type) == 1 ? 'selected' : '' }}>Gewicht winnen</option>
                                <option value="2" {{ old('modal_type', $plans->modal_type) == 2 ? 'selected' : '' }}>Gewicht verliezen</option>
                            </select>
                            @error('modal_type')
                                <div class="text-danger error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    
                    <div class="col-md-12">
                        <div class="form-group mb-4">
                            <label for="price">Prijs(€)</label>
                            <input type="text" id="price" name="price" class="form-control" placeholder="Prijs" value="{{ old('price', $plans->price ?? '') }}">
                            @error('price')
                                <div class="text-danger error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>


                    <div class="col-md-12">
                        <div class="form-group mb-4">
                            <label for="plan_type">Plantype</label>
                            <select id="plan_type" name="plan_type" class="form-control">
                                <option value="">Selecteer Plantype</option>
                                <option value="Wekelijks" {{ old('plan_type', $plans->plan_type) == 'Wekelijks' ? 'selected' : '' }}>Wekelijks</option>
                                <option value="Maandelijks" {{ old('plan_type', $plans->plan_type) == 'Maandelijks' ? 'selected' : '' }}>Maandelijks</option>
                                <option value="Jaarlijks" {{ old('plan_type', $plans->plan_type) == 'Jaarlijks' ? 'selected' : '' }}>Jaarlijks</option>
                            </select>

                            @error('plan_type')
                                <div class="text-danger error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    


                    <div class="col-md-12">
                        <div class="form-group mb-4">
                            <label for="description">Kenmerken</label>
                            <!-- <textarea id="description" style="height:49px;" name="description" class="form-control user-input" placeholder="Kenmerken van het plan">{{ old('description', is_array(json_decode($plans->description, true)) ? implode(", ", json_decode($plans->description, true)) : $plans->description ?? '') }}</textarea> -->
                            <textarea id="description" name="description" class="form-control user-input" placeholder="Kenmerken van het plan">
                               {{ old('description', is_array(json_decode($plans->description, true)) ? implode(", ", json_decode($plans->description, true)) : $plans->description ?? '') }}
                            </textarea>
                            @error('description')
                                <div class="text-danger error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mb-4">
                            <label for="ideal_for">Ideal For</label>
                            <input type="text" id="ideal_for" name="ideal_for" class="form-control" placeholder="Ideal for" value="{{ old('ideal_for', $plans->ideal_for ?? '') }}">
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
<!-- <script>
    $(document).ready(function () {

        function togglePriceField() {
            let feesType = $('#fees_type').val();

            if (feesType === 'Free') {
                $('#price').prop('disabled', true).val('0'); // Disable and set to 0
            } else if (feesType === 'Paid') {
                $('#price').prop('disabled', false); // Enable the field
            } else {
                $('#price').prop('disabled', true).val(''); // In case nothing is selected
            }
        }

        // Initial check on page load
        togglePriceField();

        // Listen for change
        $('#fees_type').on('change', function () {
            togglePriceField();
        });
    });
</script> -->

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    $(document).ready(function () {
        // Replace the textarea with CKEditor
        CKEDITOR.replace('description', {
            height: 300,
            removeButtons: '',
            allowedContent: true
        });

        // Fee type toggle logic
        function togglePriceField() {
            let feesType = $('#fees_type').val();

            if (feesType === 'Free') {
                $('#price').prop('disabled', true).val('0');
            } else if (feesType === 'Paid') {
                $('#price').prop('disabled', false);
            } else {
                $('#price').prop('disabled', true).val('');
            }
        }

        togglePriceField();

        $('#fees_type').on('change', function () {
            togglePriceField();
        });
    });
</script>

