import CaseMedia from '@/Components/ClientCase/CaseMedia'
import SectionIntro from '@/Components/ClientCase/SectionIntro'

export default function ClientProjects({ eyebrow, title, intro, projects = [] }) {
    return (
        <section className="client-projects overflow-hidden border-b border-white/20" data-case-block data-client-projects>
            <div className="client-projects__pin py-12 md:py-16" data-client-projects-pin>
                <div className="container mx-auto">
                    <SectionIntro eyebrow={eyebrow} title={title} body={intro} />
                    <div className="client-projects__viewport mt-10 md:mt-14">
                        <ol className="client-projects__track" role="list" data-client-projects-track>
                            {projects.map((project, index) => (
                                <li key={`${project.title}-${index}`} className="client-projects__item" data-client-reveal>
                                    <article className="grid h-full border-t border-white/30 pt-5 md:grid-cols-12 md:gap-8">
                                        <div className="md:col-span-8">
                                            <CaseMedia
                                                type={project.media_type}
                                                image={project.media_image}
                                                video={project.media_video}
                                                alt={project.media_alt}
                                                placeholder={project.media_placeholder}
                                                autoplay={project.media_autoplay}
                                                className="aspect-video"
                                            />
                                        </div>
                                        <div className="mt-6 md:col-span-4 md:mt-0">
                                            <p className="text-xs font-bold tracking-widest text-socies-coral">
                                                {project.eyebrow || String(index + 1).padStart(2, '0')}
                                            </p>
                                            <h3 className="mt-4 text-2xl font-bold leading-tight md:text-3xl">{project.title}</h3>
                                            {project.summary && <p className="mt-5 leading-relaxed text-gray-2">{project.summary}</p>}
                                            {project.tags?.length > 0 && (
                                                <ul className="mt-8 flex flex-wrap gap-2" aria-label="Capacidades">
                                                    {project.tags.map((tag) => <li key={tag} className="border border-white/20 px-3 py-1 text-xs text-gray-2">{tag}</li>)}
                                                </ul>
                                            )}
                                        </div>
                                    </article>
                                </li>
                            ))}
                        </ol>
                    </div>
                </div>
            </div>
        </section>
    )
}
