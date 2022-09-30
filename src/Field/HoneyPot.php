<?php

declare(strict_types=1);

namespace BeastBytes\Form\Field;

use InvalidArgumentException;
use Yiisoft\Form\Field\Base\EnrichmentFromRules\EnrichmentFromRulesInterface;
use Yiisoft\Form\Field\Base\EnrichmentFromRules\EnrichmentFromRulesTrait;
use Yiisoft\Form\Field\Base\InputField;
use Yiisoft\Form\Field\Base\Placeholder\PlaceholderInterface;
use Yiisoft\Form\Field\Base\Placeholder\PlaceholderTrait;
use Yiisoft\Form\Field\Base\ValidationClass\ValidationClassInterface;
use Yiisoft\Form\Field\Base\ValidationClass\ValidationClassTrait;
use Yiisoft\Html\Html;
use Yiisoft\Validator\BeforeValidationInterface;
use Yiisoft\Validator\Rule\HasLength;
use Yiisoft\Validator\Rule\Regex;
use Yiisoft\Validator\Rule\Required;

use function is_string;

/**
 * Generates a honey pot input that is used to help
 * detect spam form submission.
 *
 * Two inputs are generated; a hidden input with the original attribute name
 * and a text or email input with the md5 hash of the name as its name.
 * The hidden input should be empty and the visible input contain
 * data on form submission; this will not be the case
 * if the form was filled and submitted by a spam bot.
 */
final class HoneyPot extends InputField implements EnrichmentFromRulesInterface, PlaceholderInterface, ValidationClassInterface
{
    use EnrichmentFromRulesTrait;
    use PlaceholderTrait;
    use ValidationClassTrait;

    private string $type = 'text';

    public function type (string $value): self
    {
        if ($value !== 'text' || $value !== 'email') {
            throw new InvalidArgumentException(type must be 'text' or 'email');
        }

        $new = clone $this;
        $new->type = $value;
        return $new;
    }

    /**
     * Maximum length of value.
     *
     * @param int|null $value A limit on the number of characters a user can input.
     *
     * @link https://html.spec.whatwg.org/multipage/input.html#attr-input-maxlength
     * @link https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#attr-fe-maxlength
     */
    public function maxlength(?int $value): self
    {
        $new = clone $this;
        $new->inputAttributes['maxlength'] = $value;
        return $new;
    }

    /**
     * Minimum length of value.
     *
     * @param int|null $value A lower bound on the number of characters a user can input.
     *
     * @link https://html.spec.whatwg.org/multipage/input.html#attr-input-minlength
     * @link https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#attr-fe-minlength
     */
    public function minlength(?int $value): self
    {
        $new = clone $this;
        $new->inputAttributes['minlength'] = $value;
        return $new;
    }

    /**
     * Allow to specify more than one value.
     *
     * @param bool $value Whether the user is to be allowed to specify more than one value.
     *
     * @link https://html.spec.whatwg.org/multipage/input.html#attr-input-multiple
     */
    public function multiple(bool $value = true): self
    {
        $new = clone $this;
        $new->inputAttributes['multiple'] = $value;
        return $new;
    }

    /**
     * Pattern to be matched by the form control's value.
     *
     * @param string|null $value A regular expression against which the control's value.
     *
     * @link https://html.spec.whatwg.org/multipage/input.html#attr-input-pattern
     */
    public function pattern(?string $value): self
    {
        $new = clone $this;
        $new->inputAttributes['pattern'] = $value;
        return $new;
    }

    /**
     * A boolean attribute that controls whether or not the user can edit the form control.
     *
     * @param bool $value Whether to allow the value to be edited by the user.
     *
     * @link https://html.spec.whatwg.org/multipage/input.html#attr-input-readonly
     */
    public function readonly(bool $value = true): self
    {
        $new = clone $this;
        $new->inputAttributes['readonly'] = $value;
        return $new;
    }

    /**
     * A boolean attribute. When specified, the element is required.
     *
     * @param bool $value Whether the control is required for form submission.
     *
     * @link https://html.spec.whatwg.org/multipage/input.html#attr-input-required
     */
    public function required(bool $value = true): self
    {
        $new = clone $this;
        $new->inputAttributes['required'] = $value;
        return $new;
    }

    /**
     * The size of the control.
     *
     * @param int|null $value The number of characters that allow the user to see while editing the element's value.
     *
     * @link https://html.spec.whatwg.org/multipage/input.html#attr-input-size
     */
    public function size(?int $value): self
    {
        $new = clone $this;
        $new->inputAttributes['size'] = $value;
        return $new;
    }

    /**
     * @link https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#attr-fe-disabled
     */
    public function disabled(bool $disabled = true): self
    {
        $new = clone $this;
        $new->inputAttributes['disabled'] = $disabled;
        return $new;
    }

    /**
     * Identifies the element (or elements) that describes the object.
     *
     * @link https://w3c.github.io/aria/#aria-describedby
     */
    public function ariaDescribedBy(?string $value): self
    {
        $new = clone $this;
        $new->inputAttributes['aria-describedby'] = $value;
        return $new;
    }

    /**
     * Defines a string value that labels the current element.
     *
     * @link https://w3c.github.io/aria/#aria-label
     */
    public function ariaLabel(?string $value): self
    {
        $new = clone $this;
        $new->inputAttributes['aria-label'] = $value;
        return $new;
    }

    /**
     * Focus on the control (put cursor into it) when the page loads. Only one form element could be in focus
     * at the same time.
     *
     * @link https://html.spec.whatwg.org/multipage/interaction.html#attr-fe-autofocus
     */
    public function autofocus(bool $value = true): self
    {
        $new = clone $this;
        $new->inputAttributes['autofocus'] = $value;
        return $new;
    }

    /**
     * The `tabindex` attribute indicates that its element can be focused, and where it participates in sequential
     * keyboard navigation (usually with the Tab key, hence the name).
     *
     * It accepts an integer as a value, with different results depending on the integer's value:
     *
     * - A negative value (usually `tabindex="-1"`) means that the element is not reachable via sequential keyboard
     *   navigation, but could be focused with Javascript or visually. It's mostly useful to create accessible widgets
     *   with JavaScript.
     * - `tabindex="0"` means that the element should be focusable in sequential keyboard navigation, but its order is
     *   defined by the document's source order.
     * - A positive value means the element should be focusable in sequential keyboard navigation, with its order
     *   defined by the value of the number. That is, `tabindex="4"` is focused before `tabindex="5"`, but after
     *   `tabindex="3"`.
     *
     * @link https://html.spec.whatwg.org/multipage/interaction.html#attr-tabindex
     */
    public function tabIndex(?int $value): self
    {
        $new = clone $this;
        $new->inputAttributes['tabindex'] = $value;
        return $new;
    }

    /**
     * @psalm-suppress MixedAssignment,MixedArgument
     */
    protected function beforeRender(): void
    {
        parent::beforeRender();
        if ($this->enrichmentFromRules && $this->hasFormModelAndAttribute()) {
            $rules = $this
                    ->getFormModel()
                    ->getRules()[$this->getFormAttributeName()] ?? [];
            foreach ($rules as $rule) {
                if ($rule instanceof BeforeValidationInterface && $rule->getWhen() !== null) {
                    continue;
                }

                if ($rule instanceof Required) {
                    $this->inputAttributes['required'] = true;
                }

                if ($rule instanceof HasLength) {
                    if (null !== $min = $rule->getMin()) {
                        $this->inputAttributes['minlength'] = $min;
                    }
                    if (null !== $max = $rule->getMax()) {
                        $this->inputAttributes['maxlength'] = $max;
                    }
                }

                if ($rule instanceof Regex) {
                    if (!($rule->isNot())) {
                        $this->inputAttributes['pattern'] = Html::normalizeRegexpPattern(
                            $rule->getPattern()
                        );
                    }
                }
            }
        }
    }

    protected function generateInput(): string
    {
        $value = $this->getFormAttributeValue();

        if (!is_string($value) && $value !== null) {
            throw new InvalidArgumentException('Email field requires a string or null value.');
        }

        $attributes = $this->getInputAttributes();

        return Html::input($this->type, md5($this->getInputName()), $value, $attributes)->render() .
            Html::hiddenInput($this->getInputName(), '')->render();
    }

    protected function prepareContainerAttributes(array &$attributes): void
    {
        if ($this->hasFormModelAndAttribute()) {
            $this->addValidationClassToAttributes(
                $attributes,
                $this->getFormModel(),
                $this->getFormAttributeName(),
            );
        }
    }

    protected function prepareInputAttributes(array &$attributes): void
    {
        $this->preparePlaceholderInInputAttributes($attributes);
        if ($this->hasFormModelAndAttribute()) {
            $this->addInputValidationClassToAttributes(
                $attributes,
                $this->getFormModel(),
                $this->getFormAttributeName(),
            );
        }
    }
}
