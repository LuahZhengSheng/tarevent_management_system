<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventRegistrationField extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'label',
        'type',
        'required',
        'options',
        'order',
        'placeholder',
        'help_text',
        'validation_rules',
        'min_length',
        'max_length',
        'min_value',
        'max_value',
        'default_value',
        'depends_on_field',
        'depends_on_value',
    ];

    protected $casts = [
        'required' => 'boolean',
        'options' => 'array',
        'order' => 'integer',
    ];

    // Relationship
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // Generate validation rules for Laravel validator
    public function getValidationRulesArray()
    {
        $rules = [];

        if ($this->required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Type-based rules
        switch ($this->type) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'tel':
                $rules[] = 'regex:/^[0-9\+\-\(\)\s]+$/';
                break;
            case 'number':
                $rules[] = 'numeric';
                if ($this->min_value !== null) {
                    $rules[] = 'min:' . $this->min_value;
                }
                if ($this->max_value !== null) {
                    $rules[] = 'max:' . $this->max_value;
                }
                break;
            case 'date':
                $rules[] = 'date';
                break;
            case 'select':
            case 'radio':
                if ($this->options && is_array($this->options)) {
                    $rules[] = 'in:' . implode(',', $this->options);
                }
                break;
            case 'checkbox':
                $rules[] = 'array';
                break;
            case 'text':
            case 'textarea':
                $rules[] = 'string';
                if ($this->min_length) {
                    $rules[] = 'min:' . $this->min_length;
                }
                if ($this->max_length) {
                    $rules[] = 'max:' . $this->max_length;
                }
                break;
        }

        // Custom validation rules
        if ($this->validation_rules) {
            $customRules = explode('|', $this->validation_rules);
            $rules = array_merge($rules, $customRules);
        }

        return implode('|', $rules);
    }

    // Render field HTML (for dynamic form generation)
    public function renderField($value = null)
    {
        $value = $value ?? $this->default_value;
        $required = $this->required ? 'required' : '';
        $placeholder = $this->placeholder ? "placeholder=\"{$this->placeholder}\"" : '';
        
        $html = "<div class='form-group-modern'>";
        $html .= "<label for='custom_{$this->name}' class='modern-label " . ($this->required ? 'required' : '') . "'>";
        $html .= htmlspecialchars($this->label);
        $html .= "</label>";

        switch ($this->type) {
            case 'textarea':
                $html .= "<textarea class='form-control-modern' id='custom_{$this->name}' name='custom_fields[{$this->name}]' {$required} {$placeholder}>";
                $html .= htmlspecialchars($value ?? '');
                $html .= "</textarea>";
                break;

            case 'select':
                $html .= "<select class='form-select-modern' id='custom_{$this->name}' name='custom_fields[{$this->name}]' {$required}>";
                $html .= "<option value=''>Select an option</option>";
                if ($this->options && is_array($this->options)) {
                    foreach ($this->options as $option) {
                        $selected = ($value == $option) ? 'selected' : '';
                        $html .= "<option value='" . htmlspecialchars($option) . "' {$selected}>" . htmlspecialchars($option) . "</option>";
                    }
                }
                $html .= "</select>";
                break;

            case 'radio':
                if ($this->options && is_array($this->options)) {
                    foreach ($this->options as $option) {
                        $checked = ($value == $option) ? 'checked' : '';
                        $html .= "<div class='form-check'>";
                        $html .= "<input class='form-check-input' type='radio' id='custom_{$this->name}_{$option}' name='custom_fields[{$this->name}]' value='" . htmlspecialchars($option) . "' {$checked} {$required}>";
                        $html .= "<label class='form-check-label' for='custom_{$this->name}_{$option}'>" . htmlspecialchars($option) . "</label>";
                        $html .= "</div>";
                    }
                }
                break;

            case 'checkbox':
                if ($this->options && is_array($this->options)) {
                    $values = is_array($value) ? $value : [];
                    foreach ($this->options as $option) {
                        $checked = in_array($option, $values) ? 'checked' : '';
                        $html .= "<div class='form-check'>";
                        $html .= "<input class='form-check-input' type='checkbox' id='custom_{$this->name}_{$option}' name='custom_fields[{$this->name}][]' value='" . htmlspecialchars($option) . "' {$checked}>";
                        $html .= "<label class='form-check-label' for='custom_{$this->name}_{$option}'>" . htmlspecialchars($option) . "</label>";
                        $html .= "</div>";
                    }
                }
                break;

            default: // text, email, tel, number, date
                $type = $this->type;
                $html .= "<input type='{$type}' class='form-control-modern' id='custom_{$this->name}' name='custom_fields[{$this->name}]' value='" . htmlspecialchars($value ?? '') . "' {$required} {$placeholder}>";
                break;
        }

        if ($this->help_text) {
            $html .= "<small class='form-text'>" . htmlspecialchars($this->help_text) . "</small>";
        }

        $html .= "<div class='invalid-feedback'></div>";
        $html .= "</div>";

        return $html;
    }
}