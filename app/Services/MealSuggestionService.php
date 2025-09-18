<?php

namespace App\Services;
use App\Models\User;

use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Http\Requests\GetChatRequest;
use App\Http\Requests\GetSuggestionMealRequest;
use App\Http\Requests\SuggestCameaMealRequest;
use App\Http\Requests\AddMenuRequest;
use App\Http\Requests\GetMenuRequest;
use App\Http\Requests\EatenStatusRequest;
use App\Http\Requests\MealEatenListRequest;
use App\Http\Requests\MealImageGenerationRequest;
use App\Http\Requests\StatusWeeklyMealRequest;
use App\Http\Requests\SingleMealRequest;
use App\Http\Requests\ManuallyMealRequest;
use App\Http\Requests\SuggestMealRequest;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\DietPlan;
use App\Models\MenuList;

use App\Models\HealthKit;

class MealSuggestionService
{
    public function suggestMeals()
    {
        $users = User::with('details')->get();
         $allResponses = [];
          $failedUsers = [];
        // Retrieve OpenAI API key from .env file
        $api_key = env('OPENAI_API_KEY');
        foreach ($users as $user) {

            if (!$user->details) {
                continue; // Skip this user
            }

            // Prepare user data
            $dietPlanDate = Carbon::now()->format('Y-m-d');

            //  Check if a meal plan already exists for this user on this date
            $existingPlan = DietPlan::where('user_id', $user->id)
                ->whereDate('diet_plan_date', $dietPlanDate)
                ->exists();

            if ($existingPlan) {
                \Log::info("Meal already suggested for user {$user->id} on {$dietPlanDate}. Skipping...");
                continue; // Skip generating new meal
            }
        
            try {
                $response = $this->generateMealForUser($user, $api_key);

                if ($response['success']) {
                    $allResponses[] = $response['data'];
                } else {
                    $failedUsers[] = [
                        'user_id' => $user->id,
                        'error' => $response['error'],
                    ];
                }
            } catch (Exception $e) {
                \Log::error("Meal generation failed for user {$user->id}: " . $e->getMessage());

                $failedUsers[] = [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ];

                continue;
            }
        }

        //  Immediately retry for failed users once
        $retryFailures = [];
        foreach ($failedUsers as $failedUser) {
            $user = User::with('details')->find($failedUser['user_id']);
            if (!$user || !$user->details) continue;

            $dietPlanDate = Carbon::now()->format('Y-m-d');
            $existingPlan = DietPlan::where('user_id', $user->id)
                ->whereDate('diet_plan_date', $dietPlanDate)
                ->exists();

            if ($existingPlan) {
                \Log::info("Meal already suggested (retry) for user {$user->id} on {$dietPlanDate}. Skipping...");
                continue;
            }

            try {
                $response = $this->generateMealForUser($user, $api_key);

                if ($response['success']) {
                    $allResponses[] = $response['data'];
                } else {
                    $retryFailures[] = [
                        'user_id' => $user->id,
                        'error' => $response['error'],
                    ];
                }
            } catch (Exception $e) {
                \Log::error("Retry meal generation failed for user {$user->id}: " . $e->getMessage());

                $retryFailures[] = [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ];

                continue;
            }
        }

        //  Return all results after processing all users
        return response()->json([
            'status' => true,
            'message' => 'Verwerking voltooid.',
            'successfully_generated' => $allResponses,
            'final_failed_users' => $retryFailures,
        ]);
    }

    // complete method

    // public function generateMealForUser($user, $api_key)
    // {
    //     try {
    //             $userData = [
    //                 'gender' => $user->gender == 1 ? 'Man' : ($user->gender == 2 ? 'Vrouw' : ($user->gender == 3 ? 'Andere' : 'Unknown')),
    //                 'address' => $user->address ?? 'Unknown',
    //                 'date_of_birth' => $user->dob ?? 'Unknown',
    //                 'height' => $user->height ?? 'Unknown',
    //                 'weight' => $user->weight ?? 'Unknown',
    //                 'waistCircum' => $user->waist_circum ?? 'Unknown',
    //                 'neckCircum' => $user->neck_circum ?? 'Unknown',
    //                 'chest' => $user->chest ?? 'Unknown',
    //                 'hips' => $user->hips ?? 'Unknown',
    //                 'upper_leg' => $user->upper_leg ?? 'Unknown',
    //                 'upper_arm' => $user->upper_arm ?? 'Unknown',
    //                 'goal' => $user->goal ?? 'Unknown',
    //                 'target_goal_value' => $user->target_goal_value ?? 'Unknown',
    //                 'timespan' => $user->timespan ?? 'Unknown',
    //                 'activity_type' => $user->activity_type ?? 'Unknown',
    //                 'food_activity_scale' => $user->food_activity_scale ?? 'Unknown',
    //                 'activity_level' => $user->activitylevel ?? 'Unknown',
    //                 'food_preferences' => $user->food_preferences ?? 'Unknown',
    //                 'allergies' => $user->allergies ?? 'Unknown',
    //                 'favorite_foods' => $user->favorite_foods ?? 'Unknown',
    //                 'dislike_foods' => $user->dislike_foods ?? 'Unknown',
    //                 'disease' => $user->disease ?? 'Unknown',
    //             ];
            
    //             $bmr= $user->details->bmr?? 1;
    //             // For Total Calories (e.g., "2000 kcal")
    //             $caloriesString = $user->details->total_calory;
    //             $calories_per_Day = 0;
    //             if (!empty($caloriesString) && preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $caloriesString, $matches)) {
    //                 $calories_per_Day = floatval($matches[1]); // numeric part
    //                 $caloriesUnit  = $matches[2];            // unit (e.g., "kcal")
    //             }
            
    //             $steps = $user->details->needed_steps;
    //             // For Protein (e.g., "150 g")
    //             $proteinString = $user->details->protein_gm;
    //             $protein_grams = 0;
    //             if (!empty($proteinString) &&  preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $proteinString, $matches)) {
    //                 $protein_grams = floatval($matches[1]);
    //                 $proteinUnit  = $matches[2];
    //             }

    //             // For Fats (e.g., "70 g")
    //             $fatsString = $user->details->fats_gm;
    //             $fats_grams = 0;
    //             if (!empty($fatsString) &&  preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $fatsString, $matches)) {
    //                 $fats_grams = floatval($matches[1]);
    //                 $fatsUnit  = $matches[2];
    //             }

    //             // For Carbohydrates (e.g., "250 g")
    //             // NOTE: Make sure you use the correct key from your user details.
    //             $carbsString = $user->details->carbs_gm; 
    //             $carbs_grams = 0;
    //             if (!empty($carbsString) &&  preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $carbsString, $matches)) {
    //                 $carbs_grams = floatval($matches[1]);
    //                 $carbsUnit  = $matches[2];
    //             }

    //             // Calculate percentages
    //             $protein_percentage = ($protein_grams * 4 / $bmr) * 100; // Protein percentage
    //             $carbs_percentage = ($carbs_grams * 4 / $bmr) * 100;     // Carbs percentage
    //             $fats_percentage = ($fats_grams * 9 / $bmr) * 100;        // Fats percentage   

    //             $activityDistribution = $user->details->activity_distribution;
    //             // Sample food_activity_scale value
    //             $foodActivityScale = $userData['food_activity_scale'] ?? null; // "Voedsel: 65% ,exercise: 35%"

    //             // Default percentages
    //             $foodPercentage = 50;
    //             $exercisePercentage = 50;
            
    //             if (!empty($foodActivityScale)) {
    //                 // Split the string by the comma
    //                 $parts = explode(',', $foodActivityScale);

    //                 // Separate the two parts (food and exercise)
    //                 $food = '';
    //                 $exercise = '';

    //                 // Assign the values from the two parts
    //                 if (isset($parts[0]) && strpos($parts[0], ':') !== false) {
    //                     // Trim and split the first part by colon
    //                     list($label, $percentage) = explode(':', trim($parts[0]));
    //                     $food = rtrim($percentage, '%'); // Remove the '%' sign
    //                     if (is_numeric($food)) {
    //                     $foodPercentage = (int) $food;
    //                     }
    //                 }

    //                 if (isset($parts[1]) && strpos($parts[1], ':') !== false) {
    //                     // Trim and split the second part by colon
    //                     list($label, $percentage) = explode(':', trim($parts[1]));
    //                     $exercise = rtrim($percentage, '%'); // Remove the '%' sign
    //                     if (is_numeric($exercise)) {
    //                         $exercisePercentage = (int) $exercise;
    //                     }
    //                 }

    //                 // Convert percentages to numeric values
    //                 $foodPercentage = $foodPercentage; // Convert food percentage to integer
    //                 $exercisePercentage = $exercisePercentage; // Convert exercise percentage to integer

    //             }

    //             $messages = [
    //                 [
    //                     'role' => 'system',
    //                     'content' => "Based on the user's data and food activity scale in which food is $food and excercise is $exercise,  create a **full daily meal plan  based on the type given (breakfast, lunch, dinner, snacks_afterbreakfast,snacks_afterlunch,snacks_beforedinner).Each meal/snack must be **different**. Do not repeat the same dish across multiple meals.
    //                     **STRICT REQUIREMENTS:**
    //                     You must calculate the following values based on the exact quantities of the ingredients you provide:
    //                     - Calories (in **kcal** with unit)
    //                     - Energy (in **kJ** with unit)
    //                     - Protein (in **g** with unit)
    //                     - Carbohydrates (in **g** with unit)
    //                     - Fats (in **g** with unit)

    //                     For each meal and snack, provide the following information:
    //                     - 'meal': **Exact name of the dish** (e.g., 'Oatmeal with Berries', 'Grilled Chicken Salad', 'Peanut Butter Smoothie').
    //                     - 'ingredients': Required ingredients to make the meal, (e.g. 1. 50g oatmeal, 2. 1 apple, 3. 1 teaspoon cinnamon) and always in string format.Do not return an array. Each step contains one specific ingredient with quantity and unit.
    //                     - 'description': Recipe to make the meal with uses the ingredients** from the list , presented in detailed numbered steps (e.g., 1. Step one, 2. Step two, etc.) and always in string format. Include specific quantities of ingredients and detailed cooking methods. For example, if the meal is 'curry', provide the full recipe including ingredients like '200g chicken', '1 onion', '2 cloves of garlic', and the steps to prepare it.
    //                     - 'calories_in_meal': Number of calories in the meal (as a string in kcal , calculated based on the ingredients and matching the required value exactly).
    //                     - 'energy': Energy in kilojoules (kJ), (as a string,based on the same ingredients).              
    //                     - 'protein': Protein content in grams ( as a string,based on the ingredients.).
    //                     - 'carbs': Carbohydrate content in grams (as a string, based on the ingredients.).
    //                     - 'fats': Fat content in grams (as a string, based on the ingredients.).
    //                     Ensure that all values are valid and greater than zero.
                
    //                     The total daily macronutrients should approximately be:
    //                     - Calory:  g per day
    //                     - Protein:  g per day
    //                     - Carbs:  g per day
    //                     - Fats: g per day 

    //                     **VALIDATION REQUIREMENTS:**
    //                     - Total meal macros MUST equal the specified target values
    //                     - Ingredient quantities must be realistic and practical
    //                     - Recipe must be feasible to prepare
    //                     - All nutritional calculations must be accurate
                        
                        
    //                      **JSON RULES (CRITICAL):**
    //                     - Return ONLY valid JSON 
    //                     - Use double quotes for keys and values
    //                     - No explanations, no markdown, no text before or after
    //                     - No trailing commas
    //                     - JSON must be either:
    //                     1. An object with the fixed keys listed above
    //                     2. Or a single object if only one meal is needed
                        
                
    //                     Always respond in JSON format and Dutch.Make sure to include detailed cooking instructions for each meal and snack, specifying the ingredients, their quantities, and the steps to prepare them. The recipes should be clear enough for someone to follow without additional context.
    //                     You must respond with only a valid JSON object. Do not include any explanations, notes, or text outside the JSON.",

    //                     ],
    //                 [
    //                     'role' => 'user',
    //                     'content' => json_encode($userData),
    //                 ],
    //             ];


    //         try {
    //             $client = new Client([
    //                 'headers' => [
    //                     'Content-Type' => 'application/json',
    //                     'Authorization' => "Bearer {$api_key}",
    //                 ],
    //             ]);

    //             $response = $client->post('https://api.openai.com/v1/chat/completions', [
    //                 'json' => [
    //                     'model' => 'gpt-4',
    //                     'messages' => $messages,
    //                     'max_tokens' => 1500,
    //                 ],
    //             ]);

    //             $openAIResponse = json_decode($response->getBody(), true);
               
    //             $rawContent = $openAIResponse['choices'][0]['message']['content'] ?? '';
    //             \Log::info("GPT Raw: " . $rawContent);
    //             $cleanContent = trim($rawContent);
    //             \Log::info("GPT Clean: " . $cleanContent);

    //             // Sometimes GPT returns markdown ```json ... ```
    //             // Remove those if present
    //             // $cleanContent = preg_replace('/^```(json)?|```$/m', '', $cleanContent);

    //             // $mealSuggestions = json_decode($cleanContent, true);

    //             $cleanContent = preg_replace('/```(json)?/', '', $cleanContent);
    //             $cleanContent = preg_replace('/```/', '', $cleanContent);
    //             $cleanContent = trim($cleanContent);

    //             // try {
    //             //     $mealSuggestions = json_decode($cleanContent, true, 512, JSON_THROW_ON_ERROR);

    //             //     if (isset($mealSuggestions[$mealType])) {
    //             //         // Case 1: GPT returned a full plan with meal keys
    //             //         $mealData = $mealSuggestions[$mealType];
    //             //     } elseif (isset($mealSuggestions['meal'])) {
    //             //         // Case 2: GPT returned only one meal object
    //             //         $mealData = $mealSuggestions;
    //             //     } else {
    //             //         $mealData = [];
    //             //     }
    //             // } catch (\JsonException $e) {
    //             //     \Log::error("JSON parse failed: " . $e->getMessage());
    //             //     \Log::error("GPT Output: " . $cleanContent);
    //             //     $mealData = [];
    //             // }

    //             try {
    //                 $mealSuggestions = json_decode($cleanContent, true, 512, JSON_THROW_ON_ERROR);
    //             } catch (\JsonException $e) {
    //                 \Log::error("JSON parse failed: " . $e->getMessage());
    //                 \Log::error("GPT Output: " . $cleanContent);
    //                 $mealSuggestions = [];
    //             }
    //             if (json_last_error() !== JSON_ERROR_NONE) {
    //                 \Log::error('JSON decode error: ' . json_last_error_msg());
    //                 \Log::error('Raw content: ' . $cleanContent);
    //                 $mealSuggestions = [];
    //             }
    //             // Fallback suggestions...
    //             $defaultSuggestions = [
    //                 'breakfast' => [
    //                     'meal' => 'Havermout met fruit',
    //                     'ingredients' => '1. 50g havermout  2. 250ml melk of water 3. 1 appel (in stukjes) 4. Kaneel (naar smaak)',
    //                     'description' => "1. Neem 50g havermout en giet er 250ml melk of water over. 2. Voeg een gesneden appel toe aan de havermout. 3. Bestrooi de appel en havermout royaal met kaneel. 4. Magnetron de havermout gedurende 2 minuten op vol vermogen, roer goed en magnetron vervolgens nog 2 minuten. 5. Laat de havermout even rusten voordat u serveert. Bij voorkeur warm eten.",
    //                     'calories_in_meal' =>'395 Kcal',
    //                     'energy' => '1650 kJ',
    //                     'protein' =>  '14.5 gm',
    //                     'carbs' => '67 gm', 
    //                     'fats' =>'8.8 gm',
    //                 ],
    //                 'snacks_afterbreakfast' => [
    //                     'meal' => 'Appel met pindakaas',
    //                     'ingredients' => '1. middelgrote appel  2. eetlepel pindakaas 3. 1 appel (in stukjes) 4. Kaneel (naar smaak)',
    //                     'description' => 'Snijd een appel en voeg een lepel pindakaas toe.',
    //                     'calories_in_meal' =>'175 Kcal',
    //                     'energy' => '732 kJ',
    //                     'protein' =>  '4.5 gm',
    //                     'carbs' => '25 gm', 
    //                     'fats' =>'8.3 gm',
    //                 ],
    //                 'lunch' => [
    //                     'meal' => 'Quinoasalade met groenten',
    //                     'ingredients' => '1. 100g quinoa (gekookt)  2. 1/2 paprika (gesneden) 3. 50g kikkererwten (gekookt) 4. Citroensap 6. Zout en peper naar smaak 7. Olijfolie',
    //                     'description' => 'Meng quinoa met kikkererwten, paprika en een dressing van citroen.',
    //                     'calories_in_meal' =>'262 Kcal',
    //                     'energy' => '1095 kJ',
    //                     'protein' =>  '9 gm',
    //                     'carbs' => '40 gm', 
    //                     'fats' =>'7.8 gm',
    //                 ],
    //                 'snacks_afterlunch' => [
    //                     'meal' => 'Handje noten',
    //                     'ingredients' => '1. 25-30g gemengde noten (amandelen, walnoten, cashewnoten)',
    //                     'description' => 'Eet een handje gemengde noten.',
    //                     'calories_in_meal' =>'175 Kcal',
    //                     'energy' => '732 kJ',
    //                     'protein' =>  '5 gm',
    //                     'carbs' => '5 gm', 
    //                     'fats' =>'15 gm',
    //                 ],
    //                 'snacks_beforedinner' => [
    //                     'meal' => 'Yoghurt met honing',
    //                     'ingredients' => '1. 1 theelepel honing 2. (Optioneel) een paar plakjes banaan of noten',
    //                     'description' => 'Neem een kom yoghurt en voeg honing toe.',
    //                     'calories_in_meal' =>'116 Kcal',
    //                     'energy' => '485 kJ',
    //                     'protein' =>  '5 gm',
    //                     'carbs' => '12.7 gm', 
    //                     'fats' =>'5 gm',
    //                 ],
    //                 'dinner' => [
    //                     'meal' => 'Groentencurry met rijst',
    //                     'ingredients' => '1. 100g rijst (gekookt) 2. 1/2 courgette (in blokjes) 3. 1 wortel (in plakjes) 4. 50g erwten 6. 100ml kokosmelk 7. Currypoeder 8. Zout en olie',
    //                     'description' => 'Maak een curry met kokosmelk en groenten, serveer met rijst.',
    //                     'calories_in_meal' =>'451 Kcal',
    //                     'energy' => '1886 kJ',
    //                     'protein' =>  '9.2 gm',
    //                     'carbs' => '47 gm', 
    //                     'fats' =>'25.4 gm',
    //                 ],
    //             ];

    //             $dietPlanDate = Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'));

    //             $normalizedSuggestions = [];
    //             $mealTypes = ['breakfast', 'lunch', 'dinner', 'snacks_afterbreakfast', 'snacks_afterlunch', 'snacks_beforedinner'];
    //             $imageApiKey = env('OPENAI_API_KEY_IMAGE');
    //             foreach ($mealTypes as $mealType) {
    //                 // $mealData = $mealSuggestions[$mealType] ?? [];
    //                 // $mealName = $mealData['meal'] ?? $defaultSuggestions[$mealType]['meal'];

    //                 if (isset($mealSuggestions[$mealType])) {
    //                     $mealData = $mealSuggestions[$mealType];
    //                 } elseif (isset($mealSuggestions['meal'])) {
    //                     $mealData = $mealSuggestions; // single meal object
    //                 } else {
    //                     $mealData = [];
    //                 }

    //                 $mealName = $mealData['meal'] ?? $defaultSuggestions[$mealType]['meal'];


    //                 $ingredients = is_array($mealData) && isset($mealData['ingredients']) ? $mealData['ingredients'] : $defaultSuggestions[$mealType]['ingredients'];
    //                 // $nutritionResponse = $this->generateNutrition($mealName,$ingredients);

    //                 //  Generate the image here
    //                 $imageName = $this->generateImageForMeal($mealName, $imageApiKey);

    //                 // // Image generation can be added here if needed
    //                 // $imageResponse = 'default_image_url.png'; // Placeholder for now

    //                 // Fallback to default image if image generation failed or returned empty
    //                 $imageResponse = !empty($imageName) ? $imageName : 'default_image_url.png';

    //                 $normalizedSuggestions[$mealType] = [
    //                     'meal' => $mealData['meal'] ?? $defaultSuggestions[$mealType]['meal'],
    //                     'ingredients' => $mealData['ingredients'] ?? $defaultSuggestions[$mealType]['ingredients'],
    //                     'description' => $mealData['description'] ?? $defaultSuggestions[$mealType]['description'],
    //                     'calories_in_meal' => $mealData['calories_in_meal'] ?? $defaultSuggestions[$mealType]['calories_in_meal'],
    //                     'energy' => $mealData['energy'] ?? $defaultSuggestions[$mealType]['energy'],
    //                     'protein' => $mealData['protein'] ?? $defaultSuggestions[$mealType]['protein'],
    //                     'carbs' => $mealData['carbs'] ?? $defaultSuggestions[$mealType]['carbs'],
    //                     'fats' => $mealData['fats'] ?? $defaultSuggestions[$mealType]['fats'],
    //                     'image_url' => $imageResponse,
    //                     // 'nutrition' => $nutritionResponse ?? $defaultSuggestions['fats'],
    //                 ];
    //             }

    //             $dietPlan = new \App\Models\DietPlan([
    //                 'user_id' => $user->id,
    //                 'diet_plan_date' => $dietPlanDate,
    //                 'bmr' => $bmr,
    //                 'total_calory' => $calories_per_Day . ' Kcal',
    //                 'protein_gm' => $protein_grams . ' gm',
    //                 'protein_percentage' => $protein_percentage . ' %',
    //                 'carbs_gm' => $carbs_grams . ' gm',
    //                 'carbs_percentage' => $carbs_percentage . ' %',
    //                 'fats_gm' => $fats_grams . ' gm',
    //                 'fats_percentage' => $fats_percentage . ' %',
    //                 'activity_distribution' => $activityDistribution,
    //                 'needed_steps' => $steps,
    //                 'diet_plan' => json_encode($normalizedSuggestions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    //             ]);

    //             $dietPlan->save();

    //             return [
    //                 'success' => true,
    //                 'data' => [
    //                     'status' => true,
    //                     'message' => 'Maaltijdsuggesties succesvol verwerkt.',
    //                     'bmr' => $dietPlan->bmr,
    //                     'totalCaloriesPerDay' => $dietPlan->total_calory,
    //                     'macro_protein' => $dietPlan->protein_gm,
    //                     'protein_percentage' => $dietPlan->protein_percentage,
    //                     'macro_carbs' => $dietPlan->carbs_gm,
    //                     'carbs_percentage' => $dietPlan->carbs_percentage,
    //                     'macro_fats' => $dietPlan->fats_gm,
    //                     'fats_percentage' => $dietPlan->fats_percentage,
    //                     'activity_distribution' => $dietPlan->activity_distribution,
    //                     'data' => $normalizedSuggestions,
    //                 ],
    //             ];
    //         } catch (Exception $e) {
    //             return [
    //                 'success' => false,
    //                 'error' => $e->getMessage(),
    //             ];
    //         }
    //     } catch (Exception $e) {
    //         return [
    //             'success' => false,
    //             'error' => $e->getMessage(),
    //         ];
    //     }
    // }

    // with previous day meal tracking

    public function generateMealForUser($user, $api_key)
    {
        try {
                $userData = [
                    'gender' => $user->gender == 1 ? 'Man' : ($user->gender == 2 ? 'Vrouw' : ($user->gender == 3 ? 'Andere' : 'Unknown')),
                    'address' => $user->address ?? 'Unknown',
                    'date_of_birth' => $user->dob ?? 'Unknown',
                    'height' => $user->height ?? 'Unknown',
                    'weight' => $user->weight ?? 'Unknown',
                    'waistCircum' => $user->waist_circum ?? 'Unknown',
                    'neckCircum' => $user->neck_circum ?? 'Unknown',
                    'chest' => $user->chest ?? 'Unknown',
                    'hips' => $user->hips ?? 'Unknown',
                    'upper_leg' => $user->upper_leg ?? 'Unknown',
                    'upper_arm' => $user->upper_arm ?? 'Unknown',
                    'goal' => $user->goal ?? 'Unknown',
                    'target_goal_value' => $user->target_goal_value ?? 'Unknown',
                    'timespan' => $user->timespan ?? 'Unknown',
                    'activity_type' => $user->activity_type ?? 'Unknown',
                    'food_activity_scale' => $user->food_activity_scale ?? 'Unknown',
                    'activity_level' => $user->activitylevel ?? 'Unknown',
                    'food_preferences' => $user->food_preferences ?? 'Unknown',
                    'allergies' => $user->allergies ?? 'Unknown',
                    'favorite_foods' => $user->favorite_foods ?? 'Unknown',
                    'dislike_foods' => $user->dislike_foods ?? 'Unknown',
                    'disease' => $user->disease ?? 'Unknown',
                ];
            
                $bmr= $user->details->bmr?? 1;
                // For Total Calories (e.g., "2000 kcal")
                $caloriesString = $user->details->total_calory;
                $calories_per_Day = 0;
                if (!empty($caloriesString) && preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $caloriesString, $matches)) {
                    $calories_per_Day = floatval($matches[1]); // numeric part
                    $caloriesUnit  = $matches[2];            // unit (e.g., "kcal")
                }
            
                $steps = $user->details->needed_steps;
                // For Protein (e.g., "150 g")
                $proteinString = $user->details->protein_gm;
                $protein_grams = 0;
                if (!empty($proteinString) &&  preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $proteinString, $matches)) {
                    $protein_grams = floatval($matches[1]);
                    $proteinUnit  = $matches[2];
                }

                // For Fats (e.g., "70 g")
                $fatsString = $user->details->fats_gm;
                $fats_grams = 0;
                if (!empty($fatsString) &&  preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $fatsString, $matches)) {
                    $fats_grams = floatval($matches[1]);
                    $fatsUnit  = $matches[2];
                }

                // For Carbohydrates (e.g., "250 g")
                // NOTE: Make sure you use the correct key from your user details.
                $carbsString = $user->details->carbs_gm; 
                $carbs_grams = 0;
                if (!empty($carbsString) &&  preg_match('/([\d.]+)\s*([a-zA-Z]+)/', $carbsString, $matches)) {
                    $carbs_grams = floatval($matches[1]);
                    $carbsUnit  = $matches[2];
                }

                // Calculate percentages
                $protein_percentage = ($protein_grams * 4 / $bmr) * 100; // Protein percentage
                $carbs_percentage = ($carbs_grams * 4 / $bmr) * 100;     // Carbs percentage
                $fats_percentage = ($fats_grams * 9 / $bmr) * 100;        // Fats percentage   

                $activityDistribution = $user->details->activity_distribution;
                // Sample food_activity_scale value
                $foodActivityScale = $userData['food_activity_scale'] ?? null; // "Voedsel: 65% ,exercise: 35%"

                // Default percentages
                $foodPercentage = 50;
                $exercisePercentage = 50;
            
                if (!empty($foodActivityScale)) {
                    // Split the string by the comma
                    $parts = explode(',', $foodActivityScale);

                    // Separate the two parts (food and exercise)
                    $food = '';
                    $exercise = '';

                    // Assign the values from the two parts
                    if (isset($parts[0]) && strpos($parts[0], ':') !== false) {
                        // Trim and split the first part by colon
                        list($label, $percentage) = explode(':', trim($parts[0]));
                        $food = rtrim($percentage, '%'); // Remove the '%' sign
                        if (is_numeric($food)) {
                        $foodPercentage = (int) $food;
                        }
                    }

                    if (isset($parts[1]) && strpos($parts[1], ':') !== false) {
                        // Trim and split the second part by colon
                        list($label, $percentage) = explode(':', trim($parts[1]));
                        $exercise = rtrim($percentage, '%'); // Remove the '%' sign
                        if (is_numeric($exercise)) {
                            $exercisePercentage = (int) $exercise;
                        }
                    }

                    // Convert percentages to numeric values
                    $foodPercentage = $foodPercentage; // Convert food percentage to integer
                    $exercisePercentage = $exercisePercentage; // Convert exercise percentage to integer

                }

                $yesterdayPlan = \App\Models\DietPlan::where('user_id', $user->id)
                    ->orderByDesc('diet_plan_date')
                    ->skip(0) // latest
                    ->take(1) // get only last day's plan
                    ->first();

                $banList = [];
                if ($yesterdayPlan) {
                    $previousMeals = json_decode($yesterdayPlan->diet_plan, true);
                    foreach ($previousMeals as $mealType => $mealData) {
                        if (isset($mealData['meal'])) {
                            $banList[] = $mealData['meal'];
                        }
                    }
                }
                $banListString = implode(', ', $banList);


                $messages = [
                    [
                        'role' => 'system',
                        'content' => "Based on the user's data and food activity scale in which food is $food and exercise is $exercise, create a full daily meal plan for today based on the type given (breakfast, lunch, dinner, snacks_afterbreakfast, snacks_afterlunch, snacks_beforedinner). 
                        Each meal or snack must be different. Do not repeat the same dish across multiple meals or across different days. 
                        Yesterday the meals were: " . $banListString . ". 
                        Do not repeat any of those meals today.
 
                        STRICT REQUIREMENTS:
                        You must calculate the following values based on the exact quantities of the ingredients you provide:
                        - Calories (in kcal with unit)
                        - Energy (in kJ with unit)
                        - Protein (in g with unit)
                        - Carbohydrates (in g with unit)
                        - Fats (in g with unit)
 
                        For each meal and snack, provide the following information:
                        - meal: Exact name of the dish (e.g., Volkorenbrood met kaas, Haring met uitjes, Havermout met appel).
                        - ingredients: Required ingredients to make the meal, written as a string. Each step contains one specific ingredient with quantity and unit.
                        - description: Recipe to make the meal using the listed ingredients, presented in detailed numbered steps (1, 2, 3...). Must include quantities and cooking methods.
                        - calories_in_meal: Number of calories in kcal as a string, calculated from the ingredients.
                        - energy: Energy in kJ as a string.
                        - protein: Protein content in grams as a string.
                        - carbs: Carbohydrate content in grams as a string.
                        - fats: Fat content in grams as a string.
                        Ensure that all values are valid and greater than zero.
 
                        The total daily macronutrients should approximately be:
                        - Calory: g per day
                        - Protein: g per day
                        - Carbs: g per day
                        - Fats: g per day
 
                        DAY VARIATION RULES:
                        - Ensure no dishes are repeated from previous days
                        - Meals must be based on Dutch cuisine and ingredients available in the Netherlands
                        - Rotate styles (traditional Dutch, Mediterranean with local ingredients, seasonal fruits/vegetables)
                        - Meals must be practical to prepare in the Netherlands
 
                        VALIDATION REQUIREMENTS:
                        - Total meal macros must match the specified target values
                        - Ingredient quantities must be realistic and practical
                        - Recipe must be feasible to prepare
                        - All nutritional calculations must be accurate
 
                        JSON RULES (CRITICAL):
                        - Return only valid JSON
                        - Use double quotes for keys and values
                        - No explanations, no markdown, no text before or after
                        - No trailing commas
                        - JSON must be an object with the fixed keys listed above
 
                        Always respond in JSON format and Dutch. Recipes must be clear enough to follow without additional context.",

                        ],
                    [
                        'role' => 'user',
                        'content' => json_encode($userData),
                    ],
                ];


            try {
                $client = new Client([
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer {$api_key}",
                    ],
                ]);

                $response = $client->post('https://api.openai.com/v1/chat/completions', [
                    'json' => [
                        'model' => 'gpt-4',
                        'messages' => $messages,
                        'max_tokens' => 1500,
                    ],
                ]);

                $openAIResponse = json_decode($response->getBody(), true);
               
                $rawContent = $openAIResponse['choices'][0]['message']['content'] ?? '';
                \Log::info("GPT Raw: " . $rawContent);
                $cleanContent = trim($rawContent);
                \Log::info("GPT Clean: " . $cleanContent);

                // Sometimes GPT returns markdown ```json ... ```
                // Remove those if present
                // $cleanContent = preg_replace('/^```(json)?|```$/m', '', $cleanContent);

                // $mealSuggestions = json_decode($cleanContent, true);

                $cleanContent = preg_replace('/```(json)?/', '', $cleanContent);
                $cleanContent = preg_replace('/```/', '', $cleanContent);
                $cleanContent = trim($cleanContent);

                // try {
                //     $mealSuggestions = json_decode($cleanContent, true, 512, JSON_THROW_ON_ERROR);
                // } catch (\JsonException $e) {
                //     \Log::error("JSON parse failed: " . $e->getMessage());
                //     \Log::error("GPT Output: " . $cleanContent);
                //     $mealSuggestions = [];
                // }
                // if (json_last_error() !== JSON_ERROR_NONE) {
                //     \Log::error('JSON decode error: ' . json_last_error_msg());
                //     \Log::error('Raw content: ' . $cleanContent);
                //     $mealSuggestions = [];
                // }

                // for add space in macro unit id=f not start here 

                try {
                    $mealSuggestions = json_decode($cleanContent, true, 512, JSON_THROW_ON_ERROR);
                    // Normalize inline (only if it's an array)
                    if (is_array($mealSuggestions)) {
                        $pattern = '/(\d+(?:[.,]\d+)?)\s*(kcal|kj|kJ|g|gm|ml|kg|%)/i';

                        array_walk_recursive($mealSuggestions, function (&$value) use ($pattern) {
                            if (is_string($value)) {
                                $value = preg_replace($pattern, '$1 $2', $value);
                                $value = preg_replace('/\s+/', ' ', trim($value)); // clean multiple spaces
                            }
                        });
                    }
              
                } catch (\JsonException $e) {
                    \Log::error("JSON parse failed: " . $e->getMessage());
                    \Log::error("GPT Output: " . $cleanContent);
                    $mealSuggestions = [];
                }
                if (json_last_error() !== JSON_ERROR_NONE) {
                    \Log::error('JSON decode error: ' . json_last_error_msg());
                    \Log::error('Raw content: ' . $cleanContent);
                    $mealSuggestions = [];
                }

                // fpor aad space in macro unit id=f not end  here
                
                // Fallback suggestions...
                $defaultSuggestions = [
                    'breakfast' => [
                        'meal' => 'Havermout met fruit',
                        'ingredients' => '1. 50g havermout  2. 250ml melk of water 3. 1 appel (in stukjes) 4. Kaneel (naar smaak)',
                        'description' => "1. Neem 50g havermout en giet er 250ml melk of water over. 2. Voeg een gesneden appel toe aan de havermout. 3. Bestrooi de appel en havermout royaal met kaneel. 4. Magnetron de havermout gedurende 2 minuten op vol vermogen, roer goed en magnetron vervolgens nog 2 minuten. 5. Laat de havermout even rusten voordat u serveert. Bij voorkeur warm eten.",
                        'calories_in_meal' =>'395 Kcal',
                        'energy' => '1650 kJ',
                        'protein' =>  '14.5 gm',
                        'carbs' => '67 gm', 
                        'fats' =>'8.8 gm',
                    ],
                    'snacks_afterbreakfast' => [
                        'meal' => 'Appel met pindakaas',
                        'ingredients' => '1. middelgrote appel  2. eetlepel pindakaas 3. 1 appel (in stukjes) 4. Kaneel (naar smaak)',
                        'description' => 'Snijd een appel en voeg een lepel pindakaas toe.',
                        'calories_in_meal' =>'175 Kcal',
                        'energy' => '732 kJ',
                        'protein' =>  '4.5 gm',
                        'carbs' => '25 gm', 
                        'fats' =>'8.3 gm',
                    ],
                    'lunch' => [
                        'meal' => 'Quinoasalade met groenten',
                        'ingredients' => '1. 100g quinoa (gekookt)  2. 1/2 paprika (gesneden) 3. 50g kikkererwten (gekookt) 4. Citroensap 6. Zout en peper naar smaak 7. Olijfolie',
                        'description' => 'Meng quinoa met kikkererwten, paprika en een dressing van citroen.',
                        'calories_in_meal' =>'262 Kcal',
                        'energy' => '1095 kJ',
                        'protein' =>  '9 gm',
                        'carbs' => '40 gm', 
                        'fats' =>'7.8 gm',
                    ],
                    'snacks_afterlunch' => [
                        'meal' => 'Handje noten',
                        'ingredients' => '1. 25-30g gemengde noten (amandelen, walnoten, cashewnoten)',
                        'description' => 'Eet een handje gemengde noten.',
                        'calories_in_meal' =>'175 Kcal',
                        'energy' => '732 kJ',
                        'protein' =>  '5 gm',
                        'carbs' => '5 gm', 
                        'fats' =>'15 gm',
                    ],
                    'snacks_beforedinner' => [
                        'meal' => 'Yoghurt met honing',
                        'ingredients' => '1. 1 theelepel honing 2. (Optioneel) een paar plakjes banaan of noten',
                        'description' => 'Neem een kom yoghurt en voeg honing toe.',
                        'calories_in_meal' =>'116 Kcal',
                        'energy' => '485 kJ',
                        'protein' =>  '5 gm',
                        'carbs' => '12.7 gm', 
                        'fats' =>'5 gm',
                    ],
                    'dinner' => [
                        'meal' => 'Groentencurry met rijst',
                        'ingredients' => '1. 100g rijst (gekookt) 2. 1/2 courgette (in blokjes) 3. 1 wortel (in plakjes) 4. 50g erwten 6. 100ml kokosmelk 7. Currypoeder 8. Zout en olie',
                        'description' => 'Maak een curry met kokosmelk en groenten, serveer met rijst.',
                        'calories_in_meal' =>'451 Kcal',
                        'energy' => '1886 kJ',
                        'protein' =>  '9.2 gm',
                        'carbs' => '47 gm', 
                        'fats' =>'25.4 gm',
                    ],
                ];

                $dietPlanDate = Carbon::createFromFormat('d-m-Y', now()->format('d-m-Y'));

                $normalizedSuggestions = [];
                $mealTypes = ['breakfast', 'lunch', 'dinner', 'snacks_afterbreakfast', 'snacks_afterlunch', 'snacks_beforedinner'];
                $imageApiKey = env('OPENAI_API_KEY_IMAGE');
                foreach ($mealTypes as $mealType) {
                    // $mealData = $mealSuggestions[$mealType] ?? [];
                    // $mealName = $mealData['meal'] ?? $defaultSuggestions[$mealType]['meal'];

                    if (isset($mealSuggestions[$mealType])) {
                        $mealData = $mealSuggestions[$mealType];
                    } elseif (isset($mealSuggestions['meal'])) {
                        $mealData = $mealSuggestions; // single meal object
                    } else {
                        $mealData = [];
                    }

                    $mealName = $mealData['meal'] ?? $defaultSuggestions[$mealType]['meal'];


                    $ingredients = is_array($mealData) && isset($mealData['ingredients']) ? $mealData['ingredients'] : $defaultSuggestions[$mealType]['ingredients'];
                    // $nutritionResponse = $this->generateNutrition($mealName,$ingredients);

                    //  Generate the image here
                    $imageName = $this->generateImageForMeal($mealName, $imageApiKey);

                    // // Image generation can be added here if needed
                    // $imageResponse = 'default_image_url.png'; // Placeholder for now

                    // Fallback to default image if image generation failed or returned empty
                    $imageResponse = !empty($imageName) ? $imageName : 'default_image_url.png';

                    $normalizedSuggestions[$mealType] = [
                        'meal' => $mealData['meal'] ?? $defaultSuggestions[$mealType]['meal'],
                        'ingredients' => $mealData['ingredients'] ?? $defaultSuggestions[$mealType]['ingredients'],
                        'description' => $mealData['description'] ?? $defaultSuggestions[$mealType]['description'],
                        'calories_in_meal' => $mealData['calories_in_meal'] ?? $defaultSuggestions[$mealType]['calories_in_meal'],
                        'energy' => $mealData['energy'] ?? $defaultSuggestions[$mealType]['energy'],
                        'protein' => $mealData['protein'] ?? $defaultSuggestions[$mealType]['protein'],
                        'carbs' => $mealData['carbs'] ?? $defaultSuggestions[$mealType]['carbs'],
                        'fats' => $mealData['fats'] ?? $defaultSuggestions[$mealType]['fats'],
                        'image_url' => $imageResponse,
                        // 'nutrition' => $nutritionResponse ?? $defaultSuggestions['fats'],
                    ];
                }

                $dietPlan = new \App\Models\DietPlan([
                    'user_id' => $user->id,
                    'diet_plan_date' => $dietPlanDate,
                    'bmr' => $bmr,
                    'total_calory' => $calories_per_Day . ' Kcal',
                    'protein_gm' => $protein_grams . ' gm',
                    'protein_percentage' => $protein_percentage . ' %',
                    'carbs_gm' => $carbs_grams . ' gm',
                    'carbs_percentage' => $carbs_percentage . ' %',
                    'fats_gm' => $fats_grams . ' gm',
                    'fats_percentage' => $fats_percentage . ' %',
                    'activity_distribution' => $activityDistribution,
                    'needed_steps' => $steps,
                    'diet_plan' => json_encode($normalizedSuggestions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ]);

                $dietPlan->save();

                return [
                    'success' => true,
                    'data' => [
                        'status' => true,
                        'message' => 'Maaltijdsuggesties succesvol verwerkt.',
                        'bmr' => $dietPlan->bmr,
                        'totalCaloriesPerDay' => $dietPlan->total_calory,
                        'macro_protein' => $dietPlan->protein_gm,
                        'protein_percentage' => $dietPlan->protein_percentage,
                        'macro_carbs' => $dietPlan->carbs_gm,
                        'carbs_percentage' => $dietPlan->carbs_percentage,
                        'macro_fats' => $dietPlan->fats_gm,
                        'fats_percentage' => $dietPlan->fats_percentage,
                        'activity_distribution' => $dietPlan->activity_distribution,
                        'data' => $normalizedSuggestions,
                    ],
                ];
            } catch (Exception $e) {
                return [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }


    private function generateImageForMeal($mealName, $api_key_image)
    {
        try {
            $client = new \GuzzleHttp\Client([
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer {$api_key_image}",
                ],
            ]);

            $response = $client->post('https://api.openai.com/v1/images/generations', [
                'json' => [
                    'model' => 'dall-e-3',
                    'prompt' => "A delicious looking plate of {$mealName}",
                    'n' => 1,
                    'size' => '1024x1024',
                ],
            ]);

            $imageResponse = json_decode($response->getBody(), true);
            $imageUrl = $imageResponse['data'][0]['url'] ?? null;

            if ($imageUrl) {
                $imagePath = public_path('assets/meal-images/');
                if (!is_dir($imagePath)) {
                    mkdir($imagePath, 0755, true);
                }

                // Use same image name logic as API: meal name with underscores, no uniqid
                $imageName = str_replace(' ', '_', $mealName) . '.png';
                $fullPath = $imagePath . $imageName;

                // Save the image file from OpenAI
                file_put_contents($fullPath, file_get_contents($imageUrl));

                return $imageName;
            }

        } catch (\Exception $e) {
            \Log::error('Image generation failed for meal: ' . $mealName . ' | Error: ' . $e->getMessage());
        }

        return 'default_image_url.png';
    }

    // private function generateNutrition($mealName, $ingredients)
    // {
    //     $api_key = env('OPENAI_API_KEY');

    //     $client = new \GuzzleHttp\Client([
    //         'headers' => [
    //             'Content-Type' => 'application/json',
    //             'Authorization' => "Bearer {$api_key}",
    //         ],
    //     ]);

    //     $messages = [
    //         [
    //             'role' => 'system',
    //             'content' => 'Return only the nutrition facts list for: {$mealName} with ingredients: {$ingredients}. Use Dutch for all field names and values.

    //             Je moet zowel de hoeveelheid (met eenheid) als het %ADH (Aanbevolen Dagelijkse Hoeveelheid) geven voor elke voedingsstof. Als exacte gegevens niet beschikbaar zijn, gebruik dan een redelijke schatting of "0 g" / "0 mg" en "n.v.t." voor %ADH. Geef nooit null of "-" terug. Geef altijd alle velden.

    //             Gebruik exact deze Nederlandse voedingsstoffen:

    //             Vet, Verzadigd vet, Transvet, Enkelvoudig onverzadigd vet, Meervoudig onverzadigd vet, Koolhydraten, Netto koolhydraten, Vezels, Suikers, Toegevoegde suikers, Eiwit, Cholesterol, Natrium, Calcium, Magnesium, Kalium, IJzer, Zink, Fosfor, Vitamine A, Vitamine C, Thiamine (B1), Riboflavine (B2), Niacine (B3), Vitamine B6, Foliumzuur totaal, Foliumzuur uit voeding, Foliumzuur synthetisch, Vitamine B12, Vitamine D, Vitamine E, Vitamine K, Suikeralcoholen, Water.

    //             Formaat:

    //             [NUTRIENT]: [HOEVEELHEID MET EENHEID], %ADH: [WAARDE OF "n.v.t."]

    //             Geen uitleg. Alleen de lijst.',
    //             ],
    //             [
    //                 'role' => 'user',
    //                 'content' => "Meal: {$mealName}\nIngredients: {$ingredients}",
    //             ],
    //         ];

    //     try {
    //         $response = $client->post('https://api.openai.com/v1/chat/completions', [
    //             'json' => [
    //                 'model' => 'gpt-4.1',
    //                 'messages' => $messages,
    //                 'max_tokens' => 1000,
    //             ],
    //         ]);

    //         $openAIResponse = json_decode($response->getBody(), true);
    //         $nutritionFacts = $openAIResponse['choices'][0]['message']['content'];
    //         $lines = explode("\n", $nutritionFacts);

    //         $nutritionData = [];

    //         // Parse the response lines
    //         foreach ($lines as $line) {
    //             $line = trim($line);
    //             if (!$line) continue;

    //             // Match format: "Vet: 12 g, %ADH: 18%"
    //             if (preg_match('/^(.*?):\s+([\d\.,]+(?:\s*[a-zA-Zµ]+)?),\s*%ADH:\s*(.+)$/u', $line, $matches)) {
    //                 $nutritionData[] = [
    //                     'title' => trim($matches[1]),
    //                     'amount' => trim($matches[2]),
    //                     'percentage' => trim($matches[3]),
    //                 ];
    //             }
    //         }

    //         // List of required fields in Dutch (same order every time)
    //         $requiredFields = [
    //             "Vet", "Verzadigd vet", "Transvet", "Enkelvoudig onverzadigd vet", "Meervoudig onverzadigd vet",
    //             "Koolhydraten", "Netto koolhydraten", "Vezels", "Suikers", "Toegevoegde suikers",
    //             "Eiwit", "Cholesterol", "Natrium", "Calcium", "Magnesium", "Kalium", "IJzer",
    //             "Zink", "Fosfor", "Vitamine A", "Vitamine C", "Thiamine (B1)", "Riboflavine (B2)", "Niacine (B3)",
    //             "Vitamine B6", "Foliumzuur totaal", "Foliumzuur uit voeding", "Foliumzuur synthetisch",
    //             "Vitamine B12", "Vitamine D", "Vitamine E", "Vitamine K", "Suikeralcoholen", "Water"
    //         ];

    //         // Map parsed data by title for quick lookup
    //         $existingMap = array_column($nutritionData, null, 'title');

    //         // Final formatted array with fallback
    //         $finalNutritionData = [];

    //         foreach ($requiredFields as $field) {
    //             if (isset($existingMap[$field])) {
    //                 $finalNutritionData[] = $existingMap[$field];
    //             } else {
    //                 $finalNutritionData[] = [
    //                     'title' => $field,
    //                     'amount' => '0 g', // default value (optional: adjust per nutrient type)
    //                     'percentage' => 'n.v.t.'
    //                 ];
    //             }
    //         }

    //         if (empty($finalNutritionData) || !is_array($finalNutritionData)) {
    //             $finalNutritionData = [
    //                 ['title' => 'Vet', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Verzadigd vet', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Transvet', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Enkelvoudig onverzadigd vet', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Meervoudig onverzadigd vet', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Koolhydraten', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Netto koolhydraten', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Vezels', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Suikers', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Toegevoegde suikers', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Eiwitten', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Cholesterol', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Natrium', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Calcium', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Magnesium', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Kalium', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'IJzer', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Zink', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Fosfor', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Vitamine A', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Vitamine C', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Thiamine (B1)', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Riboflavine (B2)', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Niacine (B3)', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Vitamine B6', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Foliumzuur (totaal)', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Foliumzuur (voeding)', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Foliumzuur (supplement)', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Vitamine B12', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Vitamine D', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Vitamine E', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Vitamine K', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Suikeralcoholen', 'amount' => '-', 'percentage' => '-'],
    //                 ['title' => 'Water', 'amount' => '-', 'percentage' => '-'],
    //             ];
    //         }


    //         return $finalNutritionData;

    //     } catch (\Exception $e) {
    //         \Log::error('OpenAI nutrition API error: ' . $e->getMessage());
    //         return null;
    //     }
    // }

}