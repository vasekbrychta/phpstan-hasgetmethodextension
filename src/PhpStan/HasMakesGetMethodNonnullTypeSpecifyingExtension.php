<?php

declare(strict_types=1);

/**
 * @author Vasek Brychta <vaclav@brychtovi.cz>
 */

namespace VasekBrychta\PhpStan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\TypeCombinator;

class HasMakesGetMethodNonnullTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
	/** @var string */
	private $forClass;
	/** @var string */
	private $hasMethodName;
	/** @var string */
	private $removeNullMethodName;
	/** @var ReflectionProvider */
	private $reflectionProvider;
	/** @var TypeSpecifier */
	private $typeSpecifier;

	public function __construct(
		string $forClass,
		string $hasMethodName,
		string $removeNullMethodName,
		ReflectionProvider $reflectionProvider
	)
	{
		$this->forClass = $forClass;
		$this->hasMethodName = $hasMethodName;
		$this->removeNullMethodName = $removeNullMethodName;
		$this->reflectionProvider = $reflectionProvider;
	}

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

	public function getClass(): string
	{
		return $this->forClass;
	}

	public function isMethodSupported(
		MethodReflection $methodReflection,
		MethodCall $node,
		TypeSpecifierContext $context
	): bool
	{
		return $methodReflection->getName() === $this->hasMethodName
			&& !$context->null();
	}

	public function specifyTypes(
		MethodReflection $methodReflection,
		MethodCall $node,
		Scope $scope,
		TypeSpecifierContext $context
	): SpecifiedTypes
	{
		$scopeClass = $this->reflectionProvider->getClass($this->forClass);
		$methodVariants = $scopeClass->getMethod($this->removeNullMethodName, $scope)->getVariants();

		return $this->typeSpecifier->create(
			new MethodCall($node->var, $this->removeNullMethodName),
			TypeCombinator::removeNull(ParametersAcceptorSelector::selectSingle($methodVariants)->getReturnType()),
			$context
		);
	}
}
