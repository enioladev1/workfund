<?php

use App\Services\AI\AiResponseValidator;

beforeEach(function () {
    $this->validator = app(AiResponseValidator::class);
});

test('a well-formed AI response is validated successfully', function () {
    $result = $this->validator->validate(json_encode([
        'classification' => 'damaged_item',
        'confidence' => 0.91,
        'suspicious' => false,
        'conflict_detected' => false,
        'reasoning' => 'Customer reports damage.',
        'recommended_action' => 'approve',
    ]));

    expect($result)->not->toBeNull();
    expect($result->classification)->toBe('damaged_item');
    expect($result->confidence)->toBe(0.91);
});

test('a response wrapped in markdown fences is still parsed', function () {
    $result = $this->validator->validate("```json\n".json_encode([
        'classification' => 'incorrect_item',
        'confidence' => 0.7,
        'suspicious' => false,
        'conflict_detected' => false,
        'reasoning' => 'Wrong item.',
        'recommended_action' => 'approve',
    ])."\n```");

    expect($result)->not->toBeNull();
    expect($result->classification)->toBe('incorrect_item');
});

test('completely invalid json is rejected', function () {
    expect($this->validator->validate('not json at all'))->toBeNull();
});

test('a response missing required fields is rejected', function () {
    $result = $this->validator->validate(json_encode([
        'classification' => 'damaged_item',
        'confidence' => 0.9,
    ]));

    expect($result)->toBeNull();
});

test('an unrecognized classification value is rejected', function () {
    $result = $this->validator->validate(json_encode([
        'classification' => 'made_up_category',
        'confidence' => 0.9,
        'suspicious' => false,
        'conflict_detected' => false,
        'reasoning' => 'test',
        'recommended_action' => 'approve',
    ]));

    expect($result)->toBeNull();
});

test('an unrecognized recommended_action value is rejected', function () {
    $result = $this->validator->validate(json_encode([
        'classification' => 'damaged_item',
        'confidence' => 0.9,
        'suspicious' => false,
        'conflict_detected' => false,
        'reasoning' => 'test',
        'recommended_action' => 'refund_immediately',
    ]));

    expect($result)->toBeNull();
});

test('a confidence value outside 0 to 1 is rejected', function () {
    $result = $this->validator->validate(json_encode([
        'classification' => 'damaged_item',
        'confidence' => 1.5,
        'suspicious' => false,
        'conflict_detected' => false,
        'reasoning' => 'test',
        'recommended_action' => 'approve',
    ]));

    expect($result)->toBeNull();
});

test('non-boolean suspicious or conflict_detected values are rejected', function () {
    $result = $this->validator->validate(json_encode([
        'classification' => 'damaged_item',
        'confidence' => 0.9,
        'suspicious' => 'yes',
        'conflict_detected' => false,
        'reasoning' => 'test',
        'recommended_action' => 'approve',
    ]));

    expect($result)->toBeNull();
});
