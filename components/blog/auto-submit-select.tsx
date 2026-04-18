'use client'

interface Props {
  value: string
  options: Array<{ value: string; label: string }>
  name?: string
}

export function AutoSubmitSelect({ value, options, name = 'sort' }: Props) {
  return (
    <select
      name={name}
      defaultValue={value}
      onChange={e => e.currentTarget.form?.requestSubmit()}
    >
      {options.map(o => (
        <option key={o.value} value={o.value}>
          {o.label}
        </option>
      ))}
    </select>
  )
}
